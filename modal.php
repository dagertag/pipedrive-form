<?php
class PipedriveModal {
    private $jwtSecret;

    public function __construct($jwtSecret) {
        $this->jwtSecret = $jwtSecret;
    }

    public function validateRequest() {
        // Проверяем обязательные параметры
        $requiredParams = ['token', 'id', 'userId', 'companyId'];
        foreach ($requiredParams as $param) {
            if (empty($_GET[$param])) {
                throw new Exception("Missing required parameter: $param");
            }
        }

        // Валидируем JWT
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

        // Декодируем payload
        $decodedPayload = base64_decode(str_pad(strtr($payload, '-_', '+/'), strlen($payload) % 4, '=', STR_PAD_RIGHT));
        $data = json_decode($decodedPayload, true);

        // Здесь можно добавить проверку подписи если нужно
        // (для production использования)

        return $data;
    }

    public function renderModal() {
        try {
            $context = $this->validateRequest();

            // Рендерим HTML
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
            <script src="https://unpkg.com/@pipedrive/app-extensions-sdk@latest/dist/index.umd.js"></script>
            <style>
                body { margin: 0; padding: 20px; font-family: Arial; }
                .success { color: green; }
                .error { color: red; }
            </style>
        </head>
        <body>
        <h2>Advanced Pipedrive Modal</h2>

        <div id="app">
            <div id="status">Initializing...</div>
            <button onclick="testSDK()">Test SDK Commands</button>
            <div id="result"></div>
        </div>

        <script>
            const sdk = new Pipedrive.AppExtensionsSdk();

            sdk.initialize().then(() => {
                document.getElementById('status').className = 'success';
                document.getElementById('status').textContent = '✓ SDK Initialized Successfully';
            }).catch(error => {
                document.getElementById('status').className = 'error';
                document.getElementById('status').textContent = '✗ SDK Error: ' + error.message;
            });

            async function testSDK() {
                try {
                    const settings = await sdk.userSettings.get();
                    document.getElementById('result').innerHTML =
                        `<pre>User Settings: ${JSON.stringify(settings, null, 2)}</pre>`;
                } catch (error) {
                    document.getElementById('result').innerHTML =
                        `<div class="error">Error: ${error.message}</div>`;
                }
            }
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
            <title>Error</title>
        </head>
        <body>
        <h2 style="color: red;">Authentication Error</h2>
        <p><?php echo htmlspecialchars($message); ?></p>
        <p>Please check your JWT configuration in Pipedrive Developer Hub.</p>
        </body>
        </html>
        <?php
    }
}

// Использование
$modal = new PipedriveModal('your-jwt-secret');
$modal->renderModal();
?>