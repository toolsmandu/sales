<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\QrCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class QrController extends Controller
{
    public function index(): View
    {
        $qrs = QrCode::query()->latest()->get();

        return view('qr.index', [
            'qrs' => $qrs,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'qr_image' => ['required', 'image', 'max:4096'],
        ]);

        $path = $request->file('qr_image')->store('qr-codes', 'public');

        QrCode::create([
            'name' => $data['name'],
            'file_path' => $path,
        ]);

        return redirect()
            ->route('qr.scan')
            ->with('status', 'QR saved successfully.');
    }

    public function update(Request $request, QrCode $qrCode): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'qr_image' => ['nullable', 'image', 'max:4096'],
        ]);

        $payload = ['name' => $data['name']];

        if ($request->hasFile('qr_image')) {
            if ($qrCode->file_path && Storage::disk('public')->exists($qrCode->file_path)) {
                Storage::disk('public')->delete($qrCode->file_path);
            }

            $payload['file_path'] = $request->file('qr_image')->store('qr-codes', 'public');
        }

        $qrCode->update($payload);

        return redirect()
            ->route('qr.scan')
            ->with('status', 'QR updated successfully.');
    }
}
