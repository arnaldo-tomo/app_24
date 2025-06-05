<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::when(request('group'), function($query) {
                          $query->where('group', request('group'));
                      })
                      ->when(request('search'), function($query) {
                          $query->where(function($q) {
                              $q->where('key', 'like', '%' . request('search') . '%')
                                ->orWhere('description', 'like', '%' . request('search') . '%');
                          });
                      })
                      ->orderBy('group')
                      ->orderBy('key')
                      ->paginate(20);

        $groups = Setting::distinct('group')->pluck('group')->sort();

        return view('admin.settings.index', compact('settings', 'groups'));
    }

    public function create()
    {
        return view('admin.settings.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255|unique:settings,key',
            'value' => 'nullable|string',
            'type' => 'required|in:' . implode(',', array_keys(Setting::TYPES)),
            'group' => 'required|in:' . implode(',', array_keys(Setting::GROUPS)),
            'description' => 'nullable|string|max:500',
            'is_public' => 'boolean',
            'file' => 'nullable|file|max:2048', // Para type=file
        ]);

        if ($validated['type'] === 'file' && $request->hasFile('file')) {
            $validated['value'] = $request->file('file')->store('settings', 'public');
        }

        $validated['is_public'] = $request->has('is_public');

        Setting::create($validated);

        return redirect()->route('admin.settings.index')
                        ->with('success', 'Configuração criada com sucesso!');
    }

    public function edit(Setting $setting)
    {
        return view('admin.settings.edit', compact('setting'));
    }

    public function update(Request $request, Setting $setting)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255|unique:settings,key,' . $setting->id,
            'value' => 'nullable|string',
            'type' => 'required|in:' . implode(',', array_keys(Setting::TYPES)),
            'group' => 'required|in:' . implode(',', array_keys(Setting::GROUPS)),
            'description' => 'nullable|string|max:500',
            'is_public' => 'boolean',
            'file' => 'nullable|file|max:2048',
        ]);

        if ($validated['type'] === 'file' && $request->hasFile('file')) {
            // Delete old file
            if ($setting->value && Storage::disk('public')->exists($setting->value)) {
                Storage::disk('public')->delete($setting->value);
            }
            $validated['value'] = $request->file('file')->store('settings', 'public');
        }

        $validated['is_public'] = $request->has('is_public');

        $setting->update($validated);

        return redirect()->route('admin.settings.index')
                        ->with('success', 'Configuração atualizada com sucesso!');
    }

    public function destroy(Setting $setting)
    {
        // Delete file if exists
        if ($setting->type === 'file' && $setting->value && Storage::disk('public')->exists($setting->value)) {
            Storage::disk('public')->delete($setting->value);
        }

        $setting->delete();

        return redirect()->route('admin.settings.index')
                        ->with('success', 'Configuração excluída com sucesso!');
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'required|string',
        ]);

        foreach ($request->settings as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            if ($setting) {
                // Process value based on type
                $processedValue = match($setting->type) {
                    'boolean' => $value === 'on' ? '1' : '0',
                    'json' => is_array($value) ? json_encode($value) : $value,
                    default => $value
                };

                $setting->update(['value' => $processedValue]);
            }
        }

        return back()->with('success', 'Configurações atualizadas com sucesso!');
    }
}