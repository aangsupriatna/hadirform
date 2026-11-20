<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Form;
use App\Models\FormVersion;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;

class FormSchemaService
{
    public const ALLOWED_FIELD_TYPES = [
        'text',
        'paragraph',
        'email',
        'phone',
        'number',
        'date',
        'time',
        'dropdown',
        'radio',
        'checkbox',
        'signature',
        'camera',
        'selfie',
        'consent',
        'heading',
        'static_info',
    ];

    /**
     * Validate and sanitize schema array.
     */
    public function validateSchema(array $schema): array
    {
        if (empty($schema['fields']) || !is_array($schema['fields'])) {
            throw new Exception('Skema formulir harus memiliki minimal satu field.');
        }

        $validatedFields = [];
        $keys = [];

        foreach ($schema['fields'] as $index => $field) {
            if (!isset($field['type']) || !in_array($field['type'], self::ALLOWED_FIELD_TYPES, true)) {
                throw new Exception("Tipe field '{$field['type']}' pada urutan ke-{$index} tidak didukung.");
            }

            $key = Str::slug($field['key'] ?? ($field['label'] ?? "field_{$index}"), '_');
            if (empty($key)) {
                $key = "field_{$index}";
            }

            if (in_array($key, $keys, true)) {
                $key = "{$key}_" . Str::random(4);
            }
            $keys[] = $key;

            $validatedFields[] = [
                'key' => $key,
                'type' => $field['type'],
                'label' => trim($field['label'] ?? 'Tanpa Label'),
                'placeholder' => $field['placeholder'] ?? null,
                'help_text' => $field['help_text'] ?? null,
                'required' => (bool)($field['required'] ?? false),
                'max_length' => isset($field['max_length']) ? (int)$field['max_length'] : null,
                'options' => isset($field['options']) && is_array($field['options'])
                    ? array_values(array_filter(array_map('trim', $field['options'])))
                    : null,
            ];
        }

        return [
            'title' => trim($schema['title'] ?? 'Formulir'),
            'description' => trim($schema['description'] ?? ''),
            'settings' => [
                'theme_color' => isset($schema['settings']['theme_color']) && FormThemeService::has($schema['settings']['theme_color'])
                    ? (string)$schema['settings']['theme_color']
                    : 'indigo',
                'header_image' => isset($schema['settings']['header_image']) && is_string($schema['settings']['header_image']) && trim($schema['settings']['header_image']) !== ''
                    ? trim($schema['settings']['header_image'])
                    : null,
                'require_signature' => (bool)($schema['settings']['require_signature'] ?? true),
                'allow_anonymous_access' => (bool)($schema['settings']['allow_anonymous_access'] ?? true),
                'duplicate_policy' => $schema['settings']['duplicate_policy'] ?? 'none', // none, email, phone, token
                'show_receipt' => (bool)($schema['settings']['show_receipt'] ?? true),
                'consent_text' => $schema['settings']['consent_text'] ?? 'Saya menyetujui pemrosesan data kehadiran ini.',
                'enable_lookup' => (bool)($schema['settings']['enable_lookup'] ?? false),
                'allow_walk_in' => (bool)($schema['settings']['allow_walk_in'] ?? true),
            ],
            'fields' => $validatedFields,
        ];
    }

    /**
     * Publish a new immutable FormVersion.
     */
    public function publish(Form $form, array $schema, ?User $user = null): FormVersion
    {
        $sanitizedSchema = $this->validateSchema($schema);

        $latestVersion = $form->versions()->max('version_number') ?? 0;
        $nextVersion = $latestVersion + 1;

        $schemaHash = hash('sha256', json_encode($sanitizedSchema, JSON_UNESCAPED_UNICODE));

        $version = FormVersion::create([
            'form_id' => $form->id,
            'version_number' => $nextVersion,
            'schema_json' => $sanitizedSchema,
            'published_at' => now(),
            'published_by' => $user?->id,
            'schema_hash' => $schemaHash,
        ]);

        $before = [
            'status' => $form->status,
            'current_version_id' => $form->current_version_id,
        ];

        $form->update([
            'status' => 'published',
            'current_version_id' => $version->id,
            'settings_json' => $sanitizedSchema['settings'],
        ]);

        AuditLog::log('form.publish', $form, $before, [
            'status' => 'published',
            'current_version_id' => $version->id,
            'version_number' => $nextVersion,
            'schema_hash' => $schemaHash,
        ], $form->organization_id);

        return $version;
    }
}
