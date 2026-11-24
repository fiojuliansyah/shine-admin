<?php

namespace App\Http\Controllers;

use App\Models\MessageTemplate;
use Illuminate\Http\Request;

class MessageTemplateController extends Controller
{
    public function index()
    {
        $templates = MessageTemplate::orderBy('name')->get();
        $placeholders = MessageTemplate::PLACEHOLDERS;

        return view('admin.message-templates.index', compact('templates', 'placeholders'));
    }

    public function store(Request $request)
    {
        MessageTemplate::create($this->validated($request));

        return redirect()->route('message-templates.index')->with('success', 'Template pesan berhasil dibuat.');
    }

    public function update(Request $request, MessageTemplate $messageTemplate)
    {
        $messageTemplate->update($this->validated($request));

        return redirect()->route('message-templates.index')->with('success', 'Template pesan berhasil diperbarui.');
    }

    public function destroy(MessageTemplate $messageTemplate)
    {
        $messageTemplate->delete();

        return redirect()->route('message-templates.index')->with('success', 'Template pesan berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string|max:5000',
        ], [
            'name.required' => 'Nama template wajib diisi.',
            'body.required' => 'Isi pesan wajib diisi.',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
