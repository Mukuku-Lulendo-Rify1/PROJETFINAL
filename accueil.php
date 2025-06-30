<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Dentist website</title>
    <link rel="stylesheet" href="accueil.css">
    <meta name="viewport" content="width=device-width, initial-scale=1"> <!-- essentiel pour responsive -->
</head>
<body>
    <header>
        <div class="logo">
            <img src="Cross red hospital medical sign vector image on VectorStock.jpg" height="96px" alt="Logo">
            <span>C</span>ENTRE MEDICAL CRPS AMANI
        </div>

        <!-- Bouton burger -->
        <div class="burger" id="burger">
            <div></div>
            <div></div>
            <div></div>
        </div>

        <!-- Menu -->
        <nav class="menu" id="menu">
            <a href="services.php">Services</a>
            <a href="spec.php">Spécialistes</a>
            <a href="apropos.php">A propos</a>
        </nav>
    </header>

    <section class="ari">
        <div class="ari-infos">
            <h1>Bienvenue sur le site officiel de l'Hôpital Médical</h1>
            <p>
                Nous mettons à votre disposition une plateforme simple et rapide pour prendre rendez-vous avec nos specialistes.
                Consultez les horaires disponibles en temps réel, accédez aux services proposés et suivez l'évolution de vos demandes
                en toute sécurité.Notre objectif est de vous offrir un service médical de qualité, accessible et adapté à vos besoins.
            </p>
            <br>
        </div>
    </section>

    <section class="ri">
        <div class="ZOO">
            <p>
                Parce que votre santé est notre priorité absolue, et que chaque moment compte lorsqu'il s'agit de votre bien-être
                physique et mental, le centre de santé CRPS AMANI vous accueille dans le cadre professionnel, apaisant et humain,
                où une équipe dévouée de Spécialistes met tout en oeuvre pour vous offrir des soins de qualité, un accompagnement
                personnalisé et des services adaptés à vos besoins, dans le respect des normes médicales le plus exigeantes, afin 
                que vous puissiez retrouver confort, mobilité et sérenité jour après jour.
            </p>
        </div>
    </section>

    <section class="lbz">
        <div class="azz">
            <h2>Horaires d'ouverture et fermeture - Hôpital Médical CRPS AMANI </h2>
            <table>
                <thead>
                    <tr>
                        <td>Lundi - Vendredi</td>
                        <td>08h00 - 18h00</td>
                        <td>Médecine Générale, Pédiatrie, Gynécologie, Urgences</td>
                    </tr>
                    <tr>
                        <td>Samedi</td>
                        <td>08h00 - 14h00</td>
                        <td>Médecine Générale, Pédiatrie, Gynécologie, Urgences</td>
                    </tr>
                    <tr>
                        <td>Dimanche</td>
                        <td>Service Urgences uniquement</td>
                        <td>Urgences (24h/24)</td>
                    </tr>
                </thead>
            </table>
        </div>
    </section>

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
