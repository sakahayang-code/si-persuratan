<?php
// persuratan/app/helpers/Auth.php

class Auth {
    public static function check(): bool {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    public static function id(): ?int {
        return $_SESSION['user_id'] ?? null;
    }

    public static function user(): ?array {
        if (!self::check()) {
            return null;
        }

        // Cache user in request
        static $cachedUser = null;
        if ($cachedUser !== null && $cachedUser['id'] === $_SESSION['user_id']) {
            return $cachedUser;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT u.*, r.name as role_name, r.slug as role_slug 
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            WHERE u.id = :id AND u.is_active = 1 AND u.deleted_at IS NULL
        ");
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $user = $stmt->fetch();

        if (!$user) {
            self::logout();
            return null;
        }

        $cachedUser = $user;
        return $user;
    }

    public static function role(): ?string {
        $user = self::user();
        return $user['role_slug'] ?? null;
    }

    public static function hasRole($roles): bool {
        $currentRole = self::role();
        if (!$currentRole) return false;

        if (is_string($roles)) {
            $roles = [$roles];
        }

        return in_array($currentRole, $roles);
    }

    public static function can(string $permissionSlug): bool {
        $user = self::user();
        if (!$user) return false;

        // Superadmin has all permissions
        if ($user['role_slug'] === 'superadmin') {
            return true;
        }

        static $permissions = null;
        if ($permissions === null) {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                SELECT p.slug 
                FROM permissions p
                JOIN role_permissions rp ON p.id = rp.permission_id
                WHERE rp.role_id = :role_id
            ");
            $stmt->execute([':role_id' => $user['role_id']]);
            $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        return in_array($permissionSlug, $permissions);
    }

    public static function login(string $usernameOrEmail, string $password): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT u.*, r.slug as role_slug 
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            WHERE (u.username = :ident OR u.email = :ident) 
              AND u.is_active = 1 
              AND u.deleted_at IS NULL
        ");
        $stmt->execute([':ident' => $usernameOrEmail]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Regenerate session id on login to prevent fixation
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role_slug'];
            $_SESSION['user_name'] = $user['full_name'];

            AuditLog::record('LOGIN', 'auth', $user['id'], "Pengguna {$user['full_name']} ({$user['username']}) berhasil login");
            return true;
        }

        AuditLog::record('LOGIN_FAILED', 'auth', null, "Percobaan login gagal untuk: {$usernameOrEmail}");
        return false;
    }

    public static function logout(): void {
        if (self::check()) {
            AuditLog::record('LOGOUT', 'auth', self::id(), "Pengguna logout");
        }
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function csrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(?string $token): bool {
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
