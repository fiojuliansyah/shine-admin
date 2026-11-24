<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subject')->nullable(); // dipakai sebagai judul email
            $table->text('body');                  // format WhatsApp: *tebal*, _miring_
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Template bawaan = isi pesan notifikasi status sebelumnya.
        DB::table('message_templates')->insert([
            'name' => 'Update Status Lamaran (Default)',
            'subject' => 'Update Status Lamaran - {status}',
            'body' => "📢 *UPDATE STATUS LAMARAN* 📢\n\n"
                . "Halo *{nama}*,\n"
                . "Kami ingin menginformasikan bahwa terdapat update pada lamaran kerja Anda.\n\n"
                . "*Detail Lamaran:*\n"
                . "• Posisi: {posisi}\n"
                . "• Status Saat Ini: *{status}*\n\n"
                . "Terima kasih telah berpartisipasi dalam proses rekrutmen kami. Silakan pantau terus informasi selanjutnya melalui website kami.\n\n"
                . "Salam,\n"
                . "*Tim HR Ciptakarir*",
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('message_templates');
    }
};
