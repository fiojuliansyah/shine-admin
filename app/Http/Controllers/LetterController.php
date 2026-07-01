<?php

namespace App\Http\Controllers;

use DataTables;
use App\Models\Site;
use App\Models\Letter;
use App\Models\TypeLetter;
use Illuminate\Http\Request;
use App\DataTables\LettersDataTable;
use App\Models\CustomVariable;
use App\Models\LetterNumberConfig;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextBreak;

class LetterController extends Controller
{
    use \App\Traits\PdfPageImageTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(LettersDataTable $dataTable)
    {
        $sites = Site::all();
        $types = TypeLetter::all();

        return $dataTable->render('admin.letters.index', compact('sites', 'types'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $sites = Site::all();
        $types = TypeLetter::all();
        $numberConfigs = LetterNumberConfig::latest()->get();
        $editor = $request->query('editor') === 'text' ? 'text' : 'canvas';
        $view = $editor === 'text' ? 'admin.letters.create-text' : 'admin.letters.create';
        return view($view, compact('sites', 'types', 'numberConfigs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Debug: Log request data
        \Log::info('Letter Store Request:', [
            'letter_number_config_id' => $request->letter_number_config_id,
            'number_format' => $request->number_format,
            'number_prefix' => $request->number_prefix,
            'number_padding' => $request->number_padding,
            'all_data' => $request->except(['_token', 'description'])
        ]);
        
        // 1. Simpan data utama
        $letter = new Letter;
        $letter->site_id = $request->site_id;
        $letter->title = $request->title;
        $letter->type_letter_id = $request->type_letter_id;
        $letter->description = $request->description;
        $letter->editor_type = $request->editor_type === 'text' ? 'text' : 'canvas';
        $letter->letter_number_config_id = $request->letter_number_config_id ?: null;
        $letter->number_format = $request->number_format;
        $letter->number_prefix = $request->number_prefix;
        $letter->number_padding = $request->number_padding ?? 3;
        $letter->require_hrd_signature = $request->boolean('require_hrd_signature');
        $letter->require_employee_signature = $request->boolean('require_employee_signature');
        
        // Debug before save
        \Log::info('Letter before save:', [
            'letter_number_config_id' => $letter->letter_number_config_id,
            'number_format' => $letter->number_format,
            'number_prefix' => $letter->number_prefix,
            'number_padding' => $letter->number_padding
        ]);
        
        $letter->save();

        // 2. Simpan variabel kustom jika ada
        if ($request->has('custom_vars')) {
            foreach ($request->custom_vars as $var) {
                // Pastikan model CustomVariable sudah memiliki relasi atau gunakan Model langsung
                $customVar = new \App\Models\CustomVariable();
                $customVar->letter_id = $letter->id;
                $customVar->name = $var['name'];
                $customVar->variable = $var['variable'];
                $customVar->save();
            }
        }

        return redirect()->route('letters.index')
                        ->with('success', 'Letter ' . $letter->title . ' berhasil dibuat');
    }

    public function edit(Letter $letter)
    {
        $sites = Site::all();
        $customVariable = CustomVariable::where('letter_id', $letter->id)->first();
        $types = TypeLetter::all();
        $numberConfigs = LetterNumberConfig::latest()->get();
        $view = ($letter->editor_type ?? 'canvas') === 'text' ? 'admin.letters.edit-text' : 'admin.letters.edit';
        return view($view, compact('letter', 'sites', 'types', 'customVariable', 'numberConfigs'));
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'nullable|image|max:5120',
            'file'  => 'nullable|image|max:5120',
        ]);

        $file = $request->file('image') ?? $request->file('file');
        if (!$file) {
            return response()->json(['error' => 'No image uploaded'], 422);
        }

        $path = $file->store('letter-images', 'public');
        $url = asset('storage/' . $path);

        return response()->json([
            'url' => $url,
            'location' => $url,
        ]);
    }

    public function show(Letter $letter)
    {
        return view('admin.letters.show', compact('letter'));
    }

    public function printView(Letter $letter)
    {
        $description = $letter->description ?? '';
        $pages = [];
        $isFabric = false;
        $title = $letter->title ?? 'Surat';

        if ($description) {
            try {
                $parsed = json_decode($description, true);
                if (isset($parsed['pages']) && is_array($parsed['pages'])) {
                    $isFabric = true;
                    $pages = $parsed['pages'];
                }
            } catch (\Exception $e) {}
        }

        return view('admin.letters.print', compact('pages', 'isFabric', 'title', 'description'));
    }

    public function numberPreview(Letter $letter)
    {
        $letter->load('type', 'numberConfig');
        $typeLetter = $letter->type;
        $currentNumber = ($typeLetter->number ?? 0) + 1;

        if ($letter->letter_number_config_id && $letter->numberConfig) {
            $preview = $letter->numberConfig->generateNumber($currentNumber, null, null, $letter);
        } elseif ($letter->number_format) {
            $preview = $letter->generateLetterNumber($currentNumber);
        } else {
            $preview = null;
        }

        return response()->json(['preview' => $preview]);
    }

    public function pdf(Letter $letter)
    {
        $description = $letter->description ?? '';
        $pages = [];
        $isFabric = false;

        if ($description && $description !== '') {
            try {
                $parsed = json_decode($description, true);
                if (isset($parsed['pages']) && is_array($parsed['pages'])) {
                    $isFabric = true;
                    $pages = $this->savePageImages($parsed['pages']);
                }
            } catch (\Exception $e) {}
        }

        $pdf = Pdf::loadView('admin.letters.pdf', compact('letter', 'pages', 'isFabric', 'description'))
            ->setPaper([0, 0, 794, 1123], 'portrait');

        $response = $pdf->stream($letter->title . '.pdf');
        $this->cleanPageImages($pages);
        return $response;
    }


    public function update(Request $request, $id)
    {
        $letter = Letter::findOrFail($id);
        
        // Debug: Log request data
        \Log::info('Letter Update Request - ID: ' . $id, [
            'letter_number_config_id' => $request->letter_number_config_id,
            'number_format' => $request->number_format,
            'number_prefix' => $request->number_prefix,
            'number_padding' => $request->number_padding,
            'all_data' => $request->except(['_token', 'description', '_method'])
        ]);
        
        // Debug before update
        \Log::info('Letter before update:', [
            'current_config_id' => $letter->letter_number_config_id,
            'current_format' => $letter->number_format,
            'current_prefix' => $letter->number_prefix,
            'current_padding' => $letter->number_padding
        ]);
        
        // 1. Update data utama surat
        $letter->site_id = $request->site_id;
        $letter->title = $request->title;
        $letter->type_letter_id = $request->type_letter_id;
        $letter->description = $request->description;
        $letter->letter_number_config_id = $request->letter_number_config_id ?: null;
        $letter->number_format = $request->number_format;
        $letter->number_prefix = $request->number_prefix;
        $letter->number_padding = $request->number_padding ?? 3;
        $letter->require_hrd_signature = $request->boolean('require_hrd_signature');
        $letter->require_employee_signature = $request->boolean('require_employee_signature');
        
        // Debug after update, before save
        \Log::info('Letter after update, before save:', [
            'new_config_id' => $letter->letter_number_config_id,
            'new_format' => $letter->number_format,
            'new_prefix' => $letter->number_prefix,
            'new_padding' => $letter->number_padding
        ]);
        
        $letter->save();

        // 2. Hapus variabel yang diklik hapus di modal (jika ada)
        if ($request->has('delete_vars')) {
            \App\Models\CustomVariable::whereIn('id', $request->delete_vars)->delete();
        }

        // 3. Simpan variabel kustom baru yang ditambahkan dari modal
        if ($request->has('custom_vars')) {
            foreach ($request->custom_vars as $var) {
                // Pastikan model CustomVariable sudah ada
                $newVar = new \App\Models\CustomVariable();
                $newVar->letter_id = $letter->id;
                $newVar->name = $var['name'];
                $newVar->variable = $var['variable'];
                $newVar->save();
            }
        }

        return redirect()->route('letters.index')
                        ->with('success', 'Letter ' . $letter->title . ' dan variabel kustom berhasil diubah');
    }

    /**
     * Import a .docx file and convert it into a Fabric.js editor template.
     */
    public function import(Request $request)
    {
        $request->validate([
            'site_id' => 'required',
            'type_letter_id' => 'required',
            'title' => 'required|string|max:255',
            'docx' => 'required|file|mimes:docx|max:10240',
        ]);

        try {
            $description = $this->convertDocxToFabric($request->file('docx')->getRealPath());
        } catch (\Throwable $e) {
            \Log::error('Letter DOCX import failed: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal memproses file DOCX: ' . $e->getMessage());
        }

        $letter = new Letter;
        $letter->site_id = $request->site_id;
        $letter->title = $request->title;
        $letter->type_letter_id = $request->type_letter_id;
        $letter->description = $description;
        $letter->number_format = '{no}/{kode_tipe}/{romawi}/{tahun}';
        $letter->number_padding = 3;
        $letter->require_hrd_signature = $request->boolean('require_hrd_signature');
        $letter->require_employee_signature = $request->boolean('require_employee_signature');
        $letter->save();

        return redirect()->route('letters.edit', $letter->id)
            ->with('success', 'Template "' . $letter->title . '" berhasil diimpor dari DOCX. Silakan periksa lalu simpan.');
    }

    /**
     * Convert a .docx document into Fabric.js canvas JSON (one textbox per paragraph),
     * paginating onto A4-sized pages (794 x 1123 px) with 60px margins.
     */
    protected function convertDocxToFabric(string $path): string
    {
        $phpWord = IOFactory::load($path);

        $CANVAS_W = 794;
        $CANVAS_H = 1123;
        $MARGIN = 60;
        $CONTENT_W = $CANVAS_W - ($MARGIN * 2);
        $MAX_Y = $CANVAS_H - $MARGIN;

        $pages = [];
        $objects = [];
        $cursorY = $MARGIN;

        $flushPage = function () use (&$pages, &$objects, &$cursorY, $MARGIN) {
            $pages[] = [
                'id' => count($pages),
                'canvasJSON' => [
                    'version' => '5.3.1',
                    'objects' => $objects,
                    'background' => '#fff',
                ],
                'pageImage' => null,
            ];
            $objects = [];
            $cursorY = $MARGIN;
        };

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $paragraphs = $this->extractParagraph($element);

                foreach ($paragraphs as $para) {
                    $text = $para['text'];
                    $fontSize = $para['fontSize'];
                    $isEmpty = trim($text) === '';

                    // Tinggi baris mengikuti render Fabric: fontSize * lineHeight.
                    $lineHeightFactor = 1.16;
                    $lineHeightPx = $fontSize * $lineHeightFactor;

                    // Estimasi jumlah baris (wrap + line break eksplisit).
                    $charsPerLine = max(1, (int) floor($CONTENT_W / ($fontSize * 0.55)));
                    $longestLine = 0;
                    foreach (explode("\n", $text) as $ln) {
                        $longestLine = max($longestLine, mb_strlen($ln));
                    }
                    $explicitBreaks = substr_count($text, "\n");
                    $wrapLines = $isEmpty ? 1 : max(1, (int) ceil($longestLine / $charsPerLine));
                    $lineCount = $wrapLines + $explicitBreaks;

                    // Paragraf kosong cukup setengah baris agar tidak longkap-longkap.
                    $blockHeight = $isEmpty
                        ? round($lineHeightPx * 0.5)
                        : ($lineCount * $lineHeightPx);

                    if ($cursorY + $blockHeight > $MAX_Y && count($objects) > 0) {
                        $flushPage();
                    }

                    // Rata kanan & kiri (justify) dibuat rapat ke kiri sesuai permintaan.
                    $align = $para['align'] === 'justify' ? 'left' : $para['align'];

                    $objects[] = [
                        'type' => 'textbox',
                        'version' => '5.3.1',
                        'left' => $MARGIN,
                        'top' => $cursorY,
                        'width' => $CONTENT_W,
                        'text' => $text === '' ? ' ' : $text,
                        'fontSize' => $fontSize,
                        'fontFamily' => $para['fontFamily'],
                        'fontWeight' => $para['bold'] ? 'bold' : 'normal',
                        'fontStyle' => $para['italic'] ? 'italic' : 'normal',
                        'underline' => $para['underline'],
                        'fill' => $para['color'],
                        'textAlign' => $align,
                        'lineHeight' => $lineHeightFactor,
                        'padding' => 0,
                        'styles' => [],
                    ];

                    $cursorY += $blockHeight;
                }
            }
        }

        $flushPage();

        return json_encode(['pages' => $pages]);
    }

    /**
     * Normalize a PhpWord element into one or more paragraph descriptors.
     */
    protected function extractParagraph($element): array
    {
        $defaults = [
            'text' => '',
            'fontSize' => 14,
            'bold' => false,
            'italic' => false,
            'underline' => false,
            'align' => 'left',
            'color' => '#000000',
            'fontFamily' => 'Arial',
        ];

        if ($element instanceof TextBreak) {
            return [$defaults];
        }

        if ($element instanceof Text) {
            return [array_merge($defaults, $this->readRunStyle($element), [
                'text' => $element->getText() ?? '',
                'align' => $this->readParagraphAlign($element),
            ])];
        }

        if ($element instanceof TextRun) {
            $text = '';
            $style = [];
            foreach ($element->getElements() as $child) {
                if ($child instanceof Text) {
                    $text .= $child->getText() ?? '';
                    if (empty($style)) {
                        $style = $this->readRunStyle($child);
                    }
                } elseif ($child instanceof TextBreak) {
                    $text .= "\n";
                }
            }
            return [array_merge($defaults, $style, [
                'text' => $text,
                'align' => $this->readParagraphAlign($element),
            ])];
        }

        // Tabel: ratakan tiap baris jadi satu paragraf, antar sel dipisah tab.
        if ($element instanceof \PhpOffice\PhpWord\Element\Table) {
            $rows = [];
            foreach ($element->getRows() as $row) {
                $cells = [];
                foreach ($row->getCells() as $cell) {
                    $cellText = '';
                    foreach ($cell->getElements() as $cellEl) {
                        foreach ($this->extractParagraph($cellEl) as $p) {
                            $cellText .= $p['text'] . ' ';
                        }
                    }
                    $cells[] = trim($cellText);
                }
                $rows[] = array_merge($defaults, ['text' => implode("\t", $cells)]);
            }
            return $rows ?: [$defaults];
        }

        return [$defaults];
    }

    protected function readRunStyle($element): array
    {
        $style = [];
        $fontStyle = method_exists($element, 'getFontStyle') ? $element->getFontStyle() : null;
        if ($fontStyle && is_object($fontStyle)) {
            $size = $fontStyle->getSize();
            if ($size) {
                // PhpWord menyimpan ukuran dalam poin; petakan poin ke px editor.
                $style['fontSize'] = (int) round($size);
            }
            if ($fontStyle->isBold()) $style['bold'] = true;
            if ($fontStyle->isItalic()) $style['italic'] = true;
            $underline = $fontStyle->getUnderline();
            if ($underline && $underline !== 'none') $style['underline'] = true;
            $name = $fontStyle->getName();
            if ($name) $style['fontFamily'] = $name;
            $color = $fontStyle->getColor();
            if ($color) $style['color'] = '#' . ltrim($color, '#');
        }
        return $style;
    }

    protected function readParagraphAlign($element): string
    {
        $map = [
            'left' => 'left', 'start' => 'left',
            'right' => 'right', 'end' => 'right',
            'center' => 'center',
            'both' => 'justify', 'justify' => 'justify',
        ];
        $paraStyle = method_exists($element, 'getParagraphStyle') ? $element->getParagraphStyle() : null;
        if ($paraStyle && is_object($paraStyle) && method_exists($paraStyle, 'getAlignment')) {
            $align = $paraStyle->getAlignment();
            if ($align && isset($map[$align])) {
                return $map[$align];
            }
        }
        return 'left';
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $letter = Letter::findOrFail($id);
        $letter->delete();
    
        return redirect()->back()
            ->with('success', 'Data Letter berhasil dihapus');
    }

    /**
     * Duplicate the specified letter template along with its custom variables.
     */
    public function duplicate(Letter $letter)
    {
        $newLetter = $letter->replicate();
        $newLetter->title = $letter->title . ' (Copy)';
        $newLetter->created_at = now();
        $newLetter->updated_at = now();
        $newLetter->save();

        foreach ($letter->customVariables as $var) {
            $newVar = $var->replicate();
            $newVar->letter_id = $newLetter->id;
            $newVar->save();
        }

        return redirect()->route('letters.index')
            ->with('success', 'Template ' . $letter->title . ' berhasil diduplikasi');
    }
}
