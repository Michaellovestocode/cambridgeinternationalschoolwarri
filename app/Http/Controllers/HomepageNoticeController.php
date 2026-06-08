<?php

namespace App\Http\Controllers;

use App\Models\SchoolSettings;
use Illuminate\Http\Request;

class HomepageNoticeController extends Controller
{
    public function edit()
    {
        return view('admin.homepage-notice.edit', [
            'settings' => SchoolSettings::getSettings(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'homepage_notice_enabled' => ['nullable', 'boolean'],
            'homepage_notice_label' => ['required', 'string', 'max:80'],
            'homepage_notice_text' => ['required', 'string', 'max:140'],
            'homepage_notice_url' => ['required', 'string', 'max:255'],
        ]);

        $settings = SchoolSettings::getSettings();

        $settings->update([
            ...$data,
            'homepage_notice_enabled' => $request->boolean('homepage_notice_enabled'),
        ]);

        return redirect()
            ->route('admin.homepage-notice.edit')
            ->with('success', 'Homepage notice updated successfully.');
    }
}
