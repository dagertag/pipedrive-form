<?php
// Указываем браузеру, что отдаём HTML
header('Content-Type: text/html; charset=UTF-8');

// Секрет для проверки JWT
define('JWT_SECRET', 'your-jwt-secret');

class PipedriveModal {
    private $jwtSecret;

    public function __construct($jwtSecret) {
        $this->jwtSecret = $jwtSecret;
    }

    public function validateRequest() {
        $requiredParams = ['token', 'id', 'userId', 'companyId'];
        foreach ($requiredParams as $param) {
            if (empty($_GET[$param])) {
                throw new Exception("Missing required parameter: $param");
            }
        }

        $decoded = $this->validateJWT($_GET['token']);
        if (!$decoded) {
            throw new Exception("Invalid JWT token");
        }

        return $decoded;
    }

    private function validateJWT($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        list($header, $payload, $signature) = $parts;
        $decodedPayload = base64_decode(str_pad(strtr($payload, '-_', '+/'), strlen($payload) % 4, '=', STR_PAD_RIGHT));
        return json_decode($decodedPayload, true);
    }

    public function renderModal() {
        try {
            $context = $this->validateRequest();
            $this->renderHTML($context);
        } catch (Exception $e) {
            $this->renderError($e->getMessage());
        }
    }

    private function renderHTML($context) {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Pipedrive Modal</title>
        </head>
        <body>
        <h2>Pipedrive Modal Loaded</h2>
        <p>Добро пожаловать, пользователь ID: <?php echo htmlspecialchars($context['userId'] ?? 'unknown'); ?></p>

        <!-- Форма прямо здесь -->
        <form id="form">
            <label>Название:</label>
            <input type="text" name="title" value="title">
            <div>
                <button type="submit">Create job</button>
                <button type="submit">Save info</button>
            </div>
        </form>

        <script>
            console.log("JWT context:", <?php echo json_encode($context); ?>);
        </script>
        </body>
        </html>
        <?php
    }

    private function renderError($message) {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Ошибка</title>
        </head>
        <body>
        <h2 style="color: red;">Ошибка аутентификации</h2>
        <p><?php echo htmlspecialchars($message); ?></p>
        </body>
        </html>
        <?php
    }
}

// Запуск
$modal = new PipedriveModal(JWT_SECRET);
$modal->renderModal();
