<!DOCTYPE html>
<html>
    <head>
        <style>
            body {
                font-family: 'Arial', sans-serif;
                background-color: #f4f4f4;
                color: #333;
                margin: 0;
                padding: 0;
            }
            .email-container {
                max-width: 600px;
                margin: 0 auto;
                background-color: #ffffff;
                border-radius: 8px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                overflow: hidden;
            }
            .email-header {
                background-color:  #f32815;
                padding: 30px;
                color: white;
                text-align: center;
            }
            .email-header h1 {
                margin: 0;
                font-size: 24px;
            }
            .email-body {
                padding: 30px;
                text-align: center;
                font-size: 16px;
            }
            .email-body p {
                line-height: 1.6;
                color: #555;
            }
            .button {
                display: inline-block;
                padding: 12px 25px;
                margin-top: 20px;
                background-color: #f32815;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                font-weight: bold;
                font-size: 16px;
            }
            .footer {
                background-color: #f4f4f4;
                text-align: center;
                padding: 20px;
                font-size: 12px;
                color: #777;
            }
            .footer a {
                color: #1e2a3a;
                text-decoration: none;
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="email-header">
                <h4> Facture d'achat !</h4>
            </div>
            <div class="email-body">
                <p>Bonjour <strong>{{ $name }}</strong>,</p>
                <p>Veuillez prendre connaissance de cette facture d'achat :</p>
                <a href="{{ $appLink }}" class="button">Voir la facture</a>
            </div>
            <div class="footer">
                <p>Si vous avez des questions, n'hésitez pas à <a href="mailto:support@stockfri.com">nous contacter</a>.</p>
                <p>Stockfri Admin | Tous droits réservés.</p>
            </div>
        </div>
    </body>
</html>
