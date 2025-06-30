<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>À propos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"> <!-- Responsive -->
    <link rel="stylesheet" href="apropos.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="Cross red hospital medical sign vector image on VectorStock.jpg" height="96px" alt="Logo">
            <span>C</span>ENTRE MÉDICAL CRPS AMANI
        </div>

        <!-- Bouton burger -->
        <div class="burger" id="burger">
            <div></div>
            <div></div>
            <div></div>
        </div>

        <nav class="menu" id="menu">
            <a href="accueil.php">Accueil</a>
            <a href="services.php">Services</a>
            <a href="spec.php">Spécialistes</a>
        </nav>
    </header>

    <main class="rml7">
        <div class="mkk">
            <img src="Désertification médicale, permanence des soins….jpeg" width="250" style="display: block; margin: 0 auto; border-radius: 6px;" alt="Image contact">
        </div>
        <br>
        <h1 style="text-align: center;">Nous contacter</h1>
        <section class="contact" style="text-align: center;">
            <p><strong>Adresse :</strong> 12, Avenue croix rouge, Kinshasa, RDC</p>
            <p><strong>Téléphone :</strong> +243 821 000 000 / +243 970 000 000</p>
            <p><strong>Horaires :</strong> Lundi à Vendredi de 8h00 à 17h00 <br> Samedi de 8h00 à 12h00</p>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 Centre Médical CRPS AMANI - Tous droits réservés</p>
    </footer>

    <script>
        const burger = document.getElementById('burger');
        const menu = document.getElementById('menu');

        burger.addEventListener('click', () => {
            burger.classList.toggle('active');
            menu.classList.toggle('active');
        });
    </script>
</body>
</html>
