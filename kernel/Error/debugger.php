<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <style>
        :root {
            --primary: #4f46e5;
            --secondary: #ec4899;
            --error: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --success: #10b981;
            --text: #1f2937;
            --text-light: #6b7280;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            min-height: 100vh;
            background: #6b7280;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .error-container {
            width: 100%;
            max-width: 600px;
            background: white;
            border-radius: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .error-header {
            padding: 2rem;
            text-align: center;
            background: var(--error);
            color: white;
            position: relative;
        }

        .error-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .error-subtitle {
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .error-content {
            padding: 2rem;
        }

        .error-details {
            margin-bottom: 1rem;
        }

        .error-message {
            background: #fef2f2;
            border: 1px solid #fee2e2;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            color: var(--error);
            font-family: monospace;
            font-size: 0.875rem;
        }

        .error-message.warning {
            background: #fffbeb;
            border-color: #fef3c7;
            color: var(--warning);
        }

        .error-message.info {
            background: #eff6ff;
            border-color: #dbeafe;
            color: var(--info);
        }

        .error-message.success {
            background: #ecfdf5;
            border-color: #d1fae5;
            color: var(--success);
        }

        .error-steps {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 2rem;
        }

        .error-steps h3 {
            font-size: 1.1rem;
            color: var(--text);
            margin-bottom: 1rem;
        }

        .step-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
            padding: 1rem;
            background: #f9fafb;
            border-radius: 12px;
            transition: transform 0.3s ease;
        }


        .step-text h4 {
            color: var(--text);
            margin-bottom: 0.25rem;
        }

        .step-text p {
            color: var(--text-light);
            font-size: 0.875rem;
        }





        @media (max-width: 640px) {
            .error-container {
                margin: 1rem;
            }

            .error-header {
                padding: 1.5rem;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .error-container {
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(-20px, 20px) rotate(180deg); }
        }
    </style>
</head>
<body>
<div class="error-container">
    <div class="error-header">




        <h1 class="error-title">Oops! Erreur dans l'application</h1>
        <p class="error-subtitle">Une erreur est survenue lors du chargement de la page.</p>
    </div>

    <div class="error-content">
        <div class="error-details">
            <div class="error-message">
                <strong>Message :</strong> <?php echo $message; ?> (code <?php echo $code; ?>)
            </div>
        </div>
        <div class="error-details">
            <div class="error-message info">
                <strong>Fichier :</strong> <?php echo $file; ?> (ligne <?php echo $line; ?>)
            </div>
        </div>

        <div class="error-steps">
            <h3>Trace de la pile d'exécution</h3>
            <?php
            foreach (explode("\n", $trace) as $line) {
                if (strlen($line) > 0) {
                    echo "<div class='step-item'>";
                    echo "<div class='step-text'>";
                    echo "<h4>$line</h4>";
                    echo '</div>';
                    echo '</div>';
                }
            }
                ?>
        </div>
    </div>
</div>
</body>
</html>