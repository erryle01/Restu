<?php

class SettingsModel extends Model
{
    /**
     * Get all settings.
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT
                setting_key,
                setting_value,
                setting_type,
                setting_group,
                description,
                updated_at
            FROM settings
            ORDER BY setting_group ASC, setting_key ASC
        ");

        return $stmt->fetchAll();
    }


    /**
     * Get settings grouped by setting_group.
     */
    public function getGrouped(): array
    {
        $settings = $this->getAll();

        $grouped = [];

        foreach ($settings as $setting) {

            $group = $setting['setting_group'];

            if (!isset($grouped[$group])) {
                $grouped[$group] = [];
            }

            $grouped[$group][] = $setting;
        }

        return $grouped;
    }


    /**
     * Get a single setting.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $stmt = $this->db->prepare("
            SELECT setting_value
            FROM settings
            WHERE setting_key = ?
            LIMIT 1
        ");

        $stmt->execute([$key]);

        $value = $stmt->fetchColumn();

        if ($value === false) {
            return $default;
        }

        return $value;
    }


    /**
     * Get a complete setting row.
     */
    public function find(string $key): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                setting_key,
                setting_value,
                setting_type,
                setting_group,
                description,
                updated_at
            FROM settings
            WHERE setting_key = ?
            LIMIT 1
        ");

        $stmt->execute([$key]);

        $setting = $stmt->fetch();

        return $setting ?: null;
    }


    /**
     * Create or update a setting.
     */
    public function set(
        string $key,
        mixed $value,
        string $type = 'text',
        string $group = 'general',
        ?string $description = null
    ): bool {

        $existing = $this->find($key);

        if ($existing) {

            $stmt = $this->db->prepare("
                UPDATE settings
                SET
                    setting_value = ?,
                    setting_type = ?,
                    setting_group = ?,
                    description = ?
                WHERE setting_key = ?
            ");

            return $stmt->execute([
                (string) $value,
                $type,
                $group,
                $description,
                $key
            ]);
        }


        $stmt = $this->db->prepare("
            INSERT INTO settings (
                setting_key,
                setting_value,
                setting_type,
                setting_group,
                description
            )
            VALUES (?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $key,
            (string) $value,
            $type,
            $group,
            $description
        ]);
    }


    /**
     * Update multiple settings.
     */
    public function updateMany(array $settings): bool
    {
        foreach ($settings as $key => $data) {

            $this->set(
                $key,
                $data['value'] ?? '',
                $data['type'] ?? 'text',
                $data['group'] ?? 'general',
                $data['description'] ?? null
            );
        }

        return true;
    }


    /**
     * Delete a setting.
     */
    public function delete(string $key): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM settings
            WHERE setting_key = ?
        ");

        return $stmt->execute([$key]);
    }
}