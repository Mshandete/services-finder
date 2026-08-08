<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Services Finder</title>
    
    <!-- External CSS File -->
    <link rel="stylesheet" href="assets/css/about.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <!-- Include Navbar -->
    <?php include_once 'includes/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="about-hero animate-bottom">
        <h1>Kuhusu <span>Services Finder</span></h1>
        <p>Tunakuunganisha na wataalamu waliobobea na waaminifu karibu nawe kwa urahisi, kasi na usalama.</p>
    </section>

    <!-- Main Content Container -->
    <div class="about-container">
        
        <!-- Story Card -->
        <div class="about-card animate-bottom">
            <div class="about-grid">
                <div class="about-text">
                    <h2>Lengo Letu Kuu</h2>
                    <p>
                        <strong>Services Finder</strong> ni jukwaa la kisasa linalokupa suluhisho la haraka la kupata mafundi na watoa huduma mbalimbali kama vile wajenzi, mafundi umeme, mafundi bomba, mekanika, wasafishaji, na wataalamu wengine wengi.
                    </p>
                    <p>
                        Tunalenga kuondoa changamoto ya kutafuta wataalamu waaminifu kwa kutoa jukwaa ambapo unaweza kuona mifano ya kazi zao, tathmini (reviews) kutoka kwa wateja wengine, na kuwasiliana nao moja kwa moja.
                    </p>
                </div>
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=800&q=80" alt="About Us">
                </div>
            </div>
        </div>

        <!-- 1. JINSI INAVYOFANYA KAZI (HOW IT WORKS) -->
        <section class="how-it-works-section">
            <h2 class="animate-bottom">Jinsi Inavyofanya Kazi</h2>
            <div class="steps-grid">
                <div class="step-card animate-bottom delay-1">
                    <div class="step-badge">1</div>
                    <div class="icon-box"><i class="fa-solid fa-magnifying-glass"></i></div>
                    <h3>Tafuta Huduma</h3>
                    <p>Chagua aina ya huduma unayohitaji na uweke eneo ulipo ili kuona mafundi wa karibu.</p>
                </div>
                <div class="step-card animate-bottom delay-2">
                    <div class="step-badge">2</div>
                    <div class="icon-box"><i class="fa-solid fa-sliders"></i></div>
                    <h3>Chagua Mtaalamu</h3>
                    <p>Kagua kazi zao za nyuma, ada, na maoni ya wateja wengine kabla ya kufanya maamuzi.</p>
                </div>
                <div class="step-card animate-bottom delay-3">
                    <div class="step-badge">3</div>
                    <div class="icon-box"><i class="fa-solid fa-handshake"></i></div>
                    <h3>Wasiliana & Pata Huduma</h3>
                    <p>Patana na fundi, maliza kazi yako kwa utulivu na uacha tathmini (*review*).</p>
                </div>
            </div>
        </section>

        <!-- 2. MAADILI NA MISINGI YETU (CORE VALUES) -->
        <section class="values-section">
            <h2 class="animate-bottom" style="text-align: center; margin-bottom: 10px;">Maadili na Misingi Yetu</h2>
            <div class="values-grid">
                <div class="value-card animate-bottom delay-1">
                    <h3>Uaminifu & Uhakiki</h3>
                    <p>Kila mtoa huduma anapitia mchakato wa uhakiki wa nyaraka zake ili kuhakikisha usalama wa wateja wetu.</p>
                </div>
                <div class="value-card animate-bottom delay-2">
                    <h3>Kasi na Urahisi</h3>
                    <p>Tunakupa mfumo wa kisasa unaokufanya upate fundi kwa mibofyo michache tu bila kupoteza muda.</p>
                </div>
                <div class="value-card animate-bottom delay-3">
                    <h3>Uwazi wa Gharama</h3>
                    <p>Hakuna gharama zilizojificha. Unapata maelezo kamili ya huduma na kuwasiliana na fundi wako moja kwa moja.</p>
                </div>
            </div>
        </section>

<!-- 3. SHUHUDA ZA WATEJA NA MAFUNDI (NEW DESIGN) -->
        <section class="testimonials-section">
            <h2 class="animate-bottom">Wanachosema Wateja na Wataalamu</h2>
            <div class="testimonials-grid">
                
                <div class="testimonial-card-v2 animate-bottom delay-1">
                    <div class="testimonial-header">
                        <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80" alt="Client">
                        <div class="user-meta">
                            <h4>Amina Juma</h4>
                            <span class="user-role role-client"><i class="fa-solid fa-circle-check"></i> Mteja - Mbezi Beach</span>
                        </div>
                    </div>
                    <div class="stars">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                    </div>
                    <p class="quote-text">"Nilipata fundi bomba wa dharura usiku. Services Finder iliniokoa sana. Fundi alikuwa mwaminifu na mtaalamu sana!"</p>
                </div>

                <div class="testimonial-card-v2 animate-bottom delay-2">
                    <div class="testimonial-header">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=200&q=80" alt="Provider">
                        <div class="user-meta">
                            <h4>Juma Rashid</h4>
                            <span class="user-role role-provider"><i class="fa-solid fa-screwdriver-wrench"></i> Fundi Umeme - Kinondoni</span>
                        </div>
                    </div>
                    <div class="stars">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                    </div>
                    <p class="quote-text">"Tangu nianze kutumia jukwaa hili kama fundi umeme, wateja wangu wameongezeka kwa 70%. Ni mfumo mzuri sana kwetu."</p>
                </div>

            </div>
        </section>

        <!-- 4. TIMU YETU (NEW MODERN CIRCULAR DESIGN) -->
        <section class="team-section">
            <h2 class="animate-bottom">Timu Inayoongoza Mfumo</h2>
            <div class="team-grid-v2">
                
                <div class="team-card-v2 animate-bottom delay-1">
                    <div class="avatar-wrapper">
                        <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=500&q=80" alt="Founder">
                    </div>
                    <h4>Kelvin Peter</h4>
                    <span class="team-role">Mwanzilishi & Mkurugenzi</span>
                    <div class="team-socials">
                        <a href="#"><i class="fa-brands fa-linkedin-in"></i></a>
                        <a href="#"><i class="fa-brands fa-x-twitter"></i></a>
                    </div>
                </div>

                <div class="team-card-v2 animate-bottom delay-2">
                    <div class="avatar-wrapper">
                        <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=500&q=80" alt="Operations">
                    </div>
                    <h4>Sarah Hassan</h4>
                    <span class="team-role">Mkuu wa Operesheni</span>
                    <div class="team-socials">
                        <a href="#"><i class="fa-brands fa-linkedin-in"></i></a>
                        <a href="#"><i class="fa-brands fa-x-twitter"></i></a>
                    </div>
                </div>

            </div>
        </section>

        <!-- 5. WITO WA KUCHUKUA HATUA (NEW GLOWING CTA) -->
        <section class="cta-section-v2 animate-bottom">
            <div class="cta-glow-orb"></div>
            <div class="cta-content-v2">
                <h2>Uko Tayari Kuanza Nasi?</h2>
                <p>Iwe unatafuta huduma ya uhakika au wewe ni mtaalamu unayetaka kuongeza wateja, Services Finder ipo kwa ajili yako.</p>
                <div class="cta-buttons-v2">
                    <a href="index.php" class="btn-glow-primary"><i class="fa-solid fa-magnifying-glass"></i> Tafuta Huduma Sasa</a>
                    <a href="auth/register.php" class="btn-glow-outline"><i class="fa-solid fa-user-plus"></i> Jiunge Kama Mtoa Huduma</a>
                </div>
            </div>
        </section>
    </div>

    <!-- External JS File -->
    <script src="assets/js/about.js"></script>
</body>
</html>