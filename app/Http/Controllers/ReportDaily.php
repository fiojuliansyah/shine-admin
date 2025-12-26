<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Site;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\TaskProgress;
use Illuminate\Http\Request;
use App\DataTables\ReportDailyDataTable;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ReportDaily extends Controller
{
    public function index(ReportDailyDataTable $dataTable, Request $request)
    {
        $users = User::all();
        $sites = Site::all();
        $filters = [
            'is_worked' => $request->is_worked,
            'date' => $request->date,
            'user_id' => $request->user_id,
            'site_id' => $request->site_id
        ];

        $today = Carbon::now()->toDateString();

        $totalCount = TaskProgress::count();
        $todayCount = TaskProgress::where('date', $today)->count();
        return $dataTable->render('dailyReport.index', compact(
            'users',
            'sites',
            'filters',
            'totalCount',
            'todayCount',
        ));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'user_id' => ['required', 'exists:users,id'],
            'site_id' => ['required', 'exists:sites,id'],
            'is_worked' => ['required', 'in:worked,not_worked'],
            'image_before' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'image_after' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'progress_description' => ['required', 'string', 'max:500'],
        ]);

        $taskProgress = TaskProgress::findOrFail($id);

        // untuk simpan ke array datanya yg otomatis jika nilainya null akan di buang karena sudah difilter
        $images = array_filter([
            'image_before' => $request->file('image_before'),
            'image_after' => $request->file('image_after'),
        ]);

        if ($request->hasFile('image_before') || $request->hasFile('image_after')) {
            foreach ($images as $key => $image) {
                // hapus gambar lama
                $publicId = $key . "_public_id";
                if (!empty($taskProgress->$publicId)) {
                    try {
                        Cloudinary::destroy($taskProgress->$publicId);
                    } catch (\Exception $e) {
                        return back()->with("Gagal hapus gambar lama Cloudinary: " . $e->getMessage());
                    }
                }

                $upload = Cloudinary::upload($image->getRealPath(), [
                    'folder' => 'schedule',
                    'transformation' => [
                        'width' => 300,
                        'height' => 200,
                        'crop' => 'fill'
                    ]
                ]);

                // updte menggunakan relasi
                if ($key === 'image_before') {
                    $taskProgress->image_before_url = $upload->getSecurePath();
                    $taskProgress->image_before_public_id = $upload->getPublicId();
                } else {
                    $taskProgress->image_after_url = $upload->getSecurePath();
                    $taskProgress->image_after_public_id = $upload->getPublicId();
                }
            }
            $updated = $taskProgress->save();
        }

        $updated = $taskProgress->update([
            'date' => $validated['date'],
            'user_id' => $validated['user_id'],
            'site_id' => $validated['site_id'],
            'is_worked' => $validated['is_worked'],
            'progress_description' => $validated['progress_description'],
        ]);

        if ($updated) {
            return to_route('dailyReport.index')->with('success', 'updated report succesfuly');
        } else {
            return back()->with('failed', 'updated report failed');
        }
    }

    public function destroy($id)
    {
        $daily = TaskProgress::findOrFail($id);

        if ($daily->image_public_id) {
            Cloudinary::destroy($daily->image_public_id);
        }

        $daily->delete();

        return redirect()->route('dailyReport.index')
            ->with('success', 'Report successfully deleted.');
    }

    public function export(Request $request)
    {
        $filters = $request->only(['date', 'user_id', 'site_id', 'is_worked']);

        $query = TaskProgress::with(['user', 'site']);

        if (!empty($filters['date'])) {
            $query->whereDate('created_at', $filters['date']);
        }
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (!empty($filters['site_id'])) {
            $query->where('site_id', $filters['site_id']);
        }
        if (!empty($filters['is_worked'])) {
            $query->where('is_worked', $filters['is_worked'] === 'worked' ? 'worked' : 'not_worked');
        }

        $tasks = $query->get();

        $data = [
            'title' => 'Laporan Harian',
            'tasks' => $tasks
        ];

        $pdf = PDF::loadView('pdf.reportDaily', $data);

        return $pdf->stream('laporan-harian.pdf');
    }
}
