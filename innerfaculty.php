<?php
require_once __DIR__ . '/config.php';

$facr = null;
$facultyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$fname = isset($_GET['fname']) ? trim($_GET['fname']) : '';
$ldap = isset($_GET['ldap']) ? trim($_GET['ldap']) : '';

if ($facultyId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM faculty WHERE id = :id AND status = 1 LIMIT 1");
    $stmt->execute([':id' => $facultyId]);
    $facr = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif (!empty($ldap)) {
    $stmt = $pdo->prepare("SELECT * FROM faculty WHERE ldap = :ldap AND status = 1 LIMIT 1");
    $stmt->execute([':ldap' => $ldap]);
    $facr = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif (!empty($fname)) {
    $stmt = $pdo->prepare("SELECT * FROM faculty WHERE (name = :fname OR CONCAT(name, ' ', lname) = :fname) AND status = 1 LIMIT 1");
    $stmt->execute([':fname' => $fname]);
    $facr = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$facr) {
    header("Location: faculty.php");
    exit;
}

// Only Top Faculty (Core Faculty, Adjunct / Visiting Faculty, Professor of Practice: top_order > 0) have inner profile pages
$topOrder = (int)($facr['top_order'] ?? 0);
if ($topOrder <= 0) {
    // Associate faculty (cluster) do not have inner pages
    $personalPage = trim($facr['personal_page'] ?? '');
    if (!empty($personalPage) && preg_match('/^https?:\/\//i', $personalPage)) {
        header("Location: " . $personalPage);
        exit;
    }
    header("Location: faculty.php");
    exit;
}

// If top faculty member has an external profile link specified
$pageType = strtolower(trim($facr['page_type'] ?? ''));
if ($pageType === 'personal' || $pageType === 'link') {
    $personalPage = trim($facr['personal_page'] ?? '');
    if (!empty($personalPage) && preg_match('/^https?:\/\//i', $personalPage)) {
        header("Location: " . $personalPage);
        exit;
    }
}

// Format email cleanly for display and mailto link
$cleanEmail = str_replace(['[at]', '[.]', '(at)', '(dot)'], ['@', '.', '@', '.'], $facr['email'] ?? '');

// Fetch publications from dedicated tables
$indPublications = [];
$journalPublications = [];
$confPublications = [];

try {
    $pubStmt = $pdo->prepare("SELECT * FROM indpublication WHERE (id = :fac_id OR faculty_id = :fac_id) ORDER BY year DESC, pub_id DESC");
    $pubStmt->execute([':fac_id' => $facr['id']]);
    $indPublications = $pubStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $indPublications = [];
}

try {
    $journalStmt = $pdo->prepare("SELECT * FROM journalpub WHERE (id = :fac_id OR faculty_id = :fac_id) ORDER BY year DESC, pub_id DESC");
    $journalStmt->execute([':fac_id' => $facr['id']]);
    $journalPublications = $journalStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $journalPublications = [];
}

try {
    $confStmt = $pdo->prepare("SELECT * FROM confrncepub WHERE (id = :fac_id OR faculty_id = :fac_id) ORDER BY year DESC, pub_id DESC");
    $confStmt->execute([':fac_id' => $facr['id']]);
    $confPublications = $confStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $confPublications = [];
}

include('header.php');
?>

<style>
    :root {
        --sidis-primary: #184C74;
        --blue-01-100: #1f3c44;
    }

    body {
        padding-top: 165px !important;
        background-color: #ffffff;
    }

    .faculty-top-wrapper {
        padding-top: 45px !important;
        padding-bottom: 35px;
        background: #ffffff;
    }

    .frame-162,
    .frame-162 * {
        box-sizing: border-box;
    }

    .frame-162 {
        display: flex;
        flex-direction: row;
        gap: 50px;
        align-items: center;
        justify-content: flex-start;
        position: relative;
    }

    .civil-engg-manu-santhanam-4 {
        flex-shrink: 0;
        width: 300px !important;
        height: 330px !important;
        position: relative;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .frame-88 {
        display: flex;
        flex-direction: column;
        gap: 30px;
        align-items: flex-start;
        justify-content: flex-start;
        flex: 1;
        position: relative;
    }

    .frame-87 {
        display: flex;
        flex-direction: column;
        gap: 30px;
        align-items: flex-start;
        justify-content: flex-start;
        position: relative;
        width: 100%;
    }

    .frame-82 {
        display: flex;
        flex-direction: column;
        gap: 5px;
        align-items: flex-start;
        justify-content: flex-start;
        position: relative;
    }

    .ravindra-naik-bukke {
        color: #000000;
        text-align: left;
        font-family: "Lato", sans-serif;
        font-size: 24px;
        letter-spacing: 0.05em;
        font-weight: 700;
        position: relative;
    }

    .assistant-professor {
        color: #000000;
        text-align: left;
        font-family: "Lato", sans-serif;
        font-size: 18px;
        letter-spacing: 0.05em;
        font-weight: 400;
        position: relative;
    }

    .frame-161 {
        display: flex;
        flex-direction: column;
        gap: 16px;
        align-items: flex-start;
        justify-content: flex-start;
        position: relative;
        width: 100%;
    }

    .frame-83,
    .frame-84,
    .frame-85 {
        display: flex;
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
        justify-content: flex-start;
        position: relative;
        width: 100%;
    }

    .frame-81 {
        display: flex;
        flex-direction: row;
        gap: 10px;
        align-items: flex-start;
        justify-content: flex-start;
        position: relative;
        width: 100%;
    }

    .heroicons-building-office,
    .vector,
    .vector2,
    .ic-outline-call,
    .material-symbols-mail-outline,
    .mdi-link-variant,
    .fa-6-brands-google-scholar {
        flex-shrink: 0;
        width: 22px !important;
        height: 22px !important;
        position: relative;
        overflow: visible;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    ._521-ed-building {
        color: #000000;
        text-align: left;
        font-family: "Lato", sans-serif;
        font-size: 16px;
        letter-spacing: 0.05em;
        font-weight: 400;
        position: relative;
        max-width: 726px;
        width: 100%;
    }

    .b-tech-in-materials-science-and-engineering-from-iit-kanpur,
    .ph-d-in-the-department-of-information-display-from-kyung-hee-university-south-korea {
        color: #000000;
        text-align: left;
        font-family: "Lato", sans-serif;
        font-size: 16px;
        letter-spacing: 0.05em;
        font-weight: 400;
        position: relative;
        max-width: 726px;
        width: 100%;
        line-height: 1.5;
    }

    .line-17 {
        margin-top: -1px;
        border-style: dashed;
        border-color: var(--blue-01-100, #1f3c44);
        border-width: 1px 0 0 0;
        width: 719px;
        max-width: 100%;
        height: 0px;
        position: relative;
    }

    .frame-852 {
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        gap: 25px;
        align-items: flex-start;
        justify-content: flex-start;
        position: relative;
    }

    .frame-842,
    .frame-853,
    .frame-86,
    .frame-872 {
        display: flex;
        flex-direction: row;
        gap: 10px;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    ._044-2257-2303,
    .ravindra-iitm-ac-in {
        color: #000000;
        text-align: left;
        font-family: "Lato", sans-serif;
        font-size: 16px;
        letter-spacing: 0.05em;
        font-weight: 400;
        position: relative;
    }

    .personal-website,
    .google-scholar {
        color: #000000;
        text-align: left;
        font-family: "Lato", sans-serif;
        font-size: 14px;
        letter-spacing: 0.05em;
        font-weight: 400;
        position: relative;
    }

    .frame-852 a {
        color: #000000;
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .frame-852 a:hover {
        color: var(--sidis-primary);
        text-decoration: underline;
    }

    /* Accordion Section - Directly matching reference_website/innerfaculty.php */
    .faculty-accordion-wrapper {
        padding-top: 10px;
        padding-bottom: 60px;
    }

    #accordionExample.accordion .accordion-item {
        border: 1px solid #dee2e6 !important;
        border-radius: 0 !important;
        margin-bottom: 0 !important;
        border-top: none !important;
    }

    #accordionExample.accordion .accordion-item:first-child {
        border-top: 1px solid #dee2e6 !important;
    }

    #accordionExample.accordion .accordion-button {
        padding: 16px 20px !important;
        background-color: #f4f4f4 !important;
        font-family: 'Lato', sans-serif !important;
        font-size: 18px !important;
        font-weight: 500 !important;
        color: #000000 !important;
        border: none !important;
        box-shadow: none !important;
        border-radius: 0 !important;
    }

    #accordionExample.accordion .accordion-button span.acc-bar {
        border-left: 3px var(--sidis-primary) solid;
        padding-left: 10px;
        font-size: 20px;
        color: #000000;
    }

    #accordionExample.accordion .accordion-button:not(.collapsed) {
        background-color: #f4f4f4 !important;
        color: var(--sidis-primary) !important;
    }

    #accordionExample.accordion .accordion-button:not(.collapsed) span.acc-bar {
        color: var(--sidis-primary) !important;
        font-weight: 700;
        border-left: 3px var(--sidis-primary) solid;
    }

    #accordionExample.accordion .accordion-body {
        background-color: #ffffff;
        padding: 24px 28px;
        font-family: 'Lato', sans-serif;
        font-size: 18px;
        line-height: 1.7;
    }

    #accordionExample.accordion .accordion-body ul {
        margin-bottom: 0;
        padding-left: 20px;
    }

    #accordionExample.accordion .accordion-body li {
        padding: 4px 0;
        text-align: justify;
    }

    #accordionExample.accordion .accordion-body a {
        color: var(--sidis-primary, #184C74) !important;
        text-decoration: underline !important;
        text-underline-offset: 3px;
        text-decoration-thickness: 1.5px;
        font-weight: 500;
        transition: color 0.2s ease, text-decoration-color 0.2s ease;
    }

    #accordionExample.accordion .accordion-body a:hover {
        color: #0b2438 !important;
        text-decoration: underline !important;
        text-decoration-thickness: 2px;
    }

    /* Publication Items - Screenshot 1 Styling */
    .pub-entry {
        display: flex;
        flex-direction: row;
        gap: 16px;
        align-items: flex-start;
        padding: 16px 0;
        border-bottom: 1px solid #eee;
    }

    .pub-entry:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .pub-entry:first-child {
        padding-top: 0;
    }

    .pub-circle-icon {
        flex-shrink: 0;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background-color: var(--sidis-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 3px;
    }

    .pub-circle-icon svg {
        width: 18px;
        height: 18px;
        fill: #ffffff;
    }

    .pub-text-box {
        flex: 1;
    }

    .pub-title-bold {
        color: #000000;
        font-family: 'Lato', sans-serif;
        font-size: 18px;
        font-style: normal;
        font-weight: 700;
        line-height: 1.4;
        letter-spacing: 0.3px;
        padding-bottom: 6px;
    }

    .pub-info-line {
        color: #717171;
        text-align: justify;
        font-family: 'Lato', sans-serif;
        font-size: 16px;
        margin: 3px 0;
        line-height: 1.5;
    }

    .pub-info-line strong {
        color: #717171;
        font-weight: 600;
    }

    .pub-info-line span.span-data {
        color: #000000;
        font-weight: 600;
    }

    .pub-info-line a {
        color: var(--sidis-primary, #184C74) !important;
        text-decoration: underline !important;
        text-underline-offset: 3px;
        transition: color 0.2s ease;
    }

    .pub-info-line a:hover {
        color: #0b2438 !important;
    }

    /* Responsive adjustments */
    @media (max-width: 991px) {
        .frame-162 {
            flex-direction: column;
            gap: 25px;
            align-items: center;
        }

        .civil-engg-manu-santhanam-4 {
            width: 100%;
            max-width: 391px;
            height: auto;
            aspect-ratio: 4/5;
        }

        .line-17 {
            width: 100%;
        }

        .frame-852 {
            width: 100%;
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
    }

    @media (max-width: 768px) {
        .frame-88 {
            width: 100% !important;
            align-items: stretch !important;
        }

        .frame-87 {
            width: 100% !important;
        }

        .frame-82 {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
            width: 100% !important;
            text-align: center !important;
        }

        .ravindra-naik-bukke {
            text-align: center !important;
            width: 100% !important;
            display: block !important;
        }

        .assistant-professor {
            text-align: center !important;
            width: 100% !important;
            display: block !important;
        }

        .frame-161 {
            width: 100% !important;
        }

        .line-17 {
            width: 100% !important;
        }

        .frame-852 {
            width: 100% !important;
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 14px 16px !important;
            align-items: start !important;
            justify-content: flex-start !important;
        }

        .frame-842,
        .frame-853,
        .frame-86,
        .frame-872 {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: flex-start !important;
            gap: 10px !important;
            width: 100% !important;
            text-align: left !important;
        }

        .frame-842 > div,
        .frame-853 > div,
        .frame-86 > div,
        .frame-872 > div {
            text-align: left !important;
            word-break: break-word !important;
            overflow-wrap: break-word !important;
            min-width: 0 !important;
        }

        .frame-852 a {
            word-break: break-word !important;
            overflow-wrap: break-word !important;
        }

        ._044-2257-2303,
        .ravindra-iitm-ac-in,
        .personal-website,
        .google-scholar {
            font-size: 14px !important;
            text-align: left !important;
            line-height: 1.35 !important;
        }
    }

    @media (max-width: 380px) {
        .frame-852 {
            grid-template-columns: 1fr !important;
            gap: 12px !important;
        }
    }
</style>

<div class="faculty-top-wrapper">
    <div class="container">
        
        <div class="frame-162">
            
            <?php
            $imgSrc = 'images/faculty/' . (!empty($facr['image']) ? $facr['image'] : 'dummy.png');
            if (!file_exists(__DIR__ . '/' . $imgSrc)) {
                $imgSrc = 'images/faculty/dummy.png';
            }
            ?>
            <img class="civil-engg-manu-santhanam-4"
                 src="<?php echo htmlspecialchars($imgSrc, ENT_QUOTES, 'UTF-8'); ?>"
                 alt="<?php echo htmlspecialchars($facr['name'], ENT_QUOTES, 'UTF-8'); ?>" />

            <div class="frame-88">
                
                <div class="frame-87">
                    
                    <div class="frame-82">
                        <div class="ravindra-naik-bukke">
                            <?php echo htmlspecialchars($facr['name'], ENT_QUOTES, 'UTF-8'); ?>
                            <?php if (!empty($facr['lname'])): ?>
                                <?php echo htmlspecialchars($facr['lname'], ENT_QUOTES, 'UTF-8'); ?>
                            <?php endif; ?>
                            <?php if (!empty($facr['honors'])): ?>
                                <span style="font-size: 18px; font-weight: 400; color: #555;">, <?php echo htmlspecialchars($facr['honors'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="assistant-professor">
                            <?php echo htmlspecialchars($facr['designation'], ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    </div>

                    <div class="frame-161">
                        
                        <?php if (!empty($facr['office'])): ?>
                            <div class="frame-83">
                                <div class="frame-81">
                                    <img class="heroicons-building-office" src="images/faculty/icons/building.svg" alt="Office" />
                                    <div class="_521-ed-building">
                                        <?php echo htmlspecialchars($facr['office'], ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($facr['bach']) || !empty($facr['bachc'])): ?>
                            <div class="frame-84">
                                <div class="frame-81">
                                    <img class="vector" src="images/faculty/icons/education.svg" alt="Bachelor" />
                                    <div class="b-tech-in-materials-science-and-engineering-from-iit-kanpur">
                                        <?php if (!empty($facr['bach'])): ?><strong><?php echo htmlspecialchars($facr['bach'], ENT_QUOTES, 'UTF-8'); ?></strong> <?php endif; ?>
                                        <?php echo htmlspecialchars($facr['bachc'], ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($facr['mast']) || !empty($facr['mastc'])): ?>
                            <div class="frame-85">
                                <div class="frame-81">
                                    <img class="vector" src="images/faculty/icons/education.svg" alt="Master" />
                                    <div class="b-tech-in-materials-science-and-engineering-from-iit-kanpur">
                                        <?php if (!empty($facr['mast'])): ?><strong><?php echo htmlspecialchars($facr['mast'], ENT_QUOTES, 'UTF-8'); ?></strong> <?php endif; ?>
                                        <?php echo htmlspecialchars($facr['mastc'], ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($facr['phd']) || !empty($facr['phdc'])): ?>
                            <div class="frame-85">
                                <div class="frame-81">
                                    <img class="vector2" src="images/faculty/icons/education.svg" alt="PhD" />
                                    <div class="ph-d-in-the-department-of-information-display-from-kyung-hee-university-south-korea">
                                        <?php if (!empty($facr['phd'])): ?><strong><?php echo htmlspecialchars($facr['phd'], ENT_QUOTES, 'UTF-8'); ?></strong> <?php endif; ?>
                                        <?php echo htmlspecialchars($facr['phdc'], ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>

                </div>

                <div class="line-17"></div>

                <div class="frame-852">

                    <?php if (!empty($facr['phone'])): ?>
                        <div class="frame-842">
                            <img class="ic-outline-call" src="images/faculty/icons/phone.svg" alt="Phone" />
                            <div class="_044-2257-2303">
                                <a href="tel:<?php echo htmlspecialchars($facr['phone'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($facr['phone'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($cleanEmail)): ?>
                        <div class="frame-853">
                            <img class="material-symbols-mail-outline" src="images/faculty/icons/mail.svg" alt="Mail" />
                            <div class="ravindra-iitm-ac-in">
                                <a href="mailto:<?php echo htmlspecialchars($cleanEmail, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($cleanEmail, ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php 
                    $personalPage = trim($facr['personal_page'] ?? '');
                    if (!empty($personalPage) && $personalPage !== '#' && strpos($personalPage, 'innerfaculty.php') === false): 
                    ?>
                        <div class="frame-86">
                            <img class="mdi-link-variant" src="images/faculty/icons/link.svg" alt="Website" />
                            <div class="personal-website">
                                <a href="<?php echo htmlspecialchars($personalPage, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
                                    Personal Website
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($facr['scopuslink'])): ?>
                        <div class="frame-872">
                            <img class="fa-6-brands-google-scholar" src="images/faculty/icons/scholar.svg" alt="Google Scholar" />
                            <div class="google-scholar">
                                <a href="<?php echo htmlspecialchars($facr['scopuslink'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
                                    Google Scholar
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($facr['lablink'])): ?>
                        <div class="frame-86">
                            <img class="mdi-link-variant" src="images/faculty/icons/link.svg" alt="Lab Website" />
                            <div class="personal-website">
                                <a href="<?php echo htmlspecialchars($facr['lablink'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
                                    Lab Website
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php 
                    $cvLink = !empty($facr['cvpdf']) ? $facr['cvpdf'] : $facr['cv'];
                    if (!empty($cvLink)): 
                    ?>
                        <div class="frame-86">
                            <img class="mdi-link-variant" src="images/faculty/icons/link.svg" alt="CV" />
                            <div class="personal-website">
                                <a href="<?php echo htmlspecialchars($cvLink, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
                                    Curriculum Vitae
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>
</div>

<!-- Accordion Section - Touching one after another, matching reference -->
<div class="faculty-accordion-wrapper">
    <div class="container">
        
        <div class="accordion" id="accordionExample">

            <!-- Biography -->
            <?php if (!empty($facr['about1'])): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingBio">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBio" aria-expanded="false" aria-controls="collapseBio">
                            <span class="acc-bar">Biography</span>
                        </button>
                    </h2>
                    <div id="collapseBio" class="accordion-collapse collapse" aria-labelledby="headingBio" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <ul>
                                <?php
                                $lines = explode("\n", $facr['about1']);
                                foreach ($lines as $line) {
                                    $s = trim($line);
                                    if (!empty($s)) {
                                        echo '<li>' . htmlspecialchars($s, ENT_QUOTES, 'UTF-8') . '</li>';
                                    }
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Research Interests -->
            <?php if (!empty($facr['resrch'])): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingResearch">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseResearch" aria-expanded="false" aria-controls="collapseResearch">
                            <span class="acc-bar">Research Interests</span>
                        </button>
                    </h2>
                    <div id="collapseResearch" class="accordion-collapse collapse" aria-labelledby="headingResearch" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <ul>
                                <?php
                                $lines = explode("\n", $facr['resrch']);
                                foreach ($lines as $line) {
                                    $s = trim($line);
                                    if (!empty($s)) {
                                        echo '<li>' . htmlspecialchars($s, ENT_QUOTES, 'UTF-8') . '</li>';
                                    }
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Specialization & Research Group -->
            <?php if (!empty($facr['specialization']) || !empty($facr['resrchgrp'])): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingSpecialization">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSpecialization" aria-expanded="false" aria-controls="collapseSpecialization">
                            <span class="acc-bar">Specialization & Research Group</span>
                        </button>
                    </h2>
                    <div id="collapseSpecialization" class="accordion-collapse collapse" aria-labelledby="headingSpecialization" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <?php if (!empty($facr['specialization'])): ?>
                                <p><strong>Area of Specialization:</strong> <?php echo htmlspecialchars($facr['specialization'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($facr['resrchgrp'])): ?>
                                <p><strong>Research Group:</strong> <?php echo htmlspecialchars($facr['resrchgrp'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Awards and Honors -->
            <?php if (!empty($facr['award'])): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingAwards">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAwards" aria-expanded="false" aria-controls="collapseAwards">
                            <span class="acc-bar">Awards and Honors</span>
                        </button>
                    </h2>
                    <div id="collapseAwards" class="accordion-collapse collapse" aria-labelledby="headingAwards" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <ol style="padding-left: 20px; margin-bottom: 0;">
                                <?php
                                $lines = explode("\n", $facr['award']);
                                foreach ($lines as $line) {
                                    $s = trim($line);
                                    if (!empty($s)) {
                                        if (strpos($s, 'http://') === 0 || strpos($s, 'https://') === 0) {
                                            echo '<li><a href="' . htmlspecialchars($s, ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener">' . htmlspecialchars($s, ENT_QUOTES, 'UTF-8') . '</a></li>';
                                        } else {
                                            echo '<li>' . htmlspecialchars($s, ENT_QUOTES, 'UTF-8') . '</li>';
                                        }
                                    }
                                }
                                ?>
                            </ol>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Professional Experience -->
            <?php 
            $expContent = !empty($facr['Experience']) ? $facr['Experience'] : (!empty($facr['precourse']) ? $facr['precourse'] : '');
            if (!empty($expContent)): 
            ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingExp">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExp" aria-expanded="false" aria-controls="collapseExp">
                            <span class="acc-bar">Professional Experience</span>
                        </button>
                    </h2>
                    <div id="collapseExp" class="accordion-collapse collapse" aria-labelledby="headingExp" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <ul>
                                <?php
                                $lines = explode("\n", $expContent);
                                foreach ($lines as $line) {
                                    $s = trim($line);
                                    if (!empty($s)) {
                                        echo '<li>' . htmlspecialchars($s, ENT_QUOTES, 'UTF-8') . '</li>';
                                    }
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Administrative Responsibilities -->
            <?php if (!empty($facr['admnres'])): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingAdmn">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdmn" aria-expanded="false" aria-controls="collapseAdmn">
                            <span class="acc-bar">Administrative Responsibilities</span>
                        </button>
                    </h2>
                    <div id="collapseAdmn" class="accordion-collapse collapse" aria-labelledby="headingAdmn" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <ul>
                                <?php
                                $lines = explode("\n", $facr['admnres']);
                                foreach ($lines as $line) {
                                    $s = trim($line);
                                    if (!empty($s)) {
                                        echo '<li>' . htmlspecialchars($s, ENT_QUOTES, 'UTF-8') . '</li>';
                                    }
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Professional Membership -->
            <?php if (!empty($facr['prfsnmem'])): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingMembership">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMembership" aria-expanded="false" aria-controls="collapseMembership">
                            <span class="acc-bar">Professional Membership</span>
                        </button>
                    </h2>
                    <div id="collapseMembership" class="accordion-collapse collapse" aria-labelledby="headingMembership" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <ul>
                                <?php
                                $lines = explode("\n", $facr['prfsnmem']);
                                foreach ($lines as $line) {
                                    $s = trim($line);
                                    if (!empty($s)) {
                                        echo '<li>' . htmlspecialchars($s, ENT_QUOTES, 'UTF-8') . '</li>';
                                    }
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Courses Taught -->
            <?php 
            $courseContent = !empty($facr['crstaught']) ? $facr['crstaught'] : (!empty($facr['curcourse']) ? $facr['curcourse'] : '');
            if (!empty($courseContent)): 
            ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingCourses">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCourses" aria-expanded="false" aria-controls="collapseCourses">
                            <span class="acc-bar">Courses Taught</span>
                        </button>
                    </h2>
                    <div id="collapseCourses" class="accordion-collapse collapse" aria-labelledby="headingCourses" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <ul>
                                <?php
                                $lines = explode("\n", $courseContent);
                                foreach ($lines as $line) {
                                    $s = trim($line);
                                    if (!empty($s)) {
                                        echo '<li>' . htmlspecialchars($s, ENT_QUOTES, 'UTF-8') . '</li>';
                                    }
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Graduate Students -->
            <?php if (!empty($facr['student'])): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingStudents">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseStudents" aria-expanded="false" aria-controls="collapseStudents">
                            <span class="acc-bar">Graduate Students</span>
                        </button>
                    </h2>
                    <div id="collapseStudents" class="accordion-collapse collapse" aria-labelledby="headingStudents" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <ul>
                                <?php
                                $students = explode("\n", $facr['student']);
                                $links = !empty($facr['studentlink']) ? explode("\n", $facr['studentlink']) : [];
                                
                                foreach ($students as $idx => $st) {
                                    $stName = trim($st);
                                    $stLink = isset($links[$idx]) ? trim($links[$idx]) : '';
                                    if (!empty($stName)) {
                                        if (!empty($stLink)) {
                                            echo '<li><a href="' . htmlspecialchars($stLink, ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener">' . htmlspecialchars($stName, ENT_QUOTES, 'UTF-8') . '</a></li>';
                                        } else {
                                            echo '<li>' . htmlspecialchars($stName, ENT_QUOTES, 'UTF-8') . '</li>';
                                        }
                                    }
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Publications (indpublication table & faculty.publication text) -->
            <?php if (!empty($indPublications) || !empty(trim($facr['publication'] ?? ''))): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingPublications">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePublications" aria-expanded="false" aria-controls="collapsePublications">
                            <span class="acc-bar">Publications</span>
                        </button>
                    </h2>
                    <div id="collapsePublications" class="accordion-collapse collapse" aria-labelledby="headingPublications" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <?php if (!empty($indPublications)): ?>
                                <div class="publication-entries">
                                    <?php foreach ($indPublications as $pub): ?>
                                        <div class="pub-entry">
                                            <div class="pub-circle-icon">
                                                <svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                                            </div>
                                            <div class="pub-text-box">
                                                <div class="pub-title-bold"><?php echo htmlspecialchars($pub['title'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                
                                                <?php if (!empty($pub['author'])): ?>
                                                    <p class="pub-info-line"><strong>Author:</strong> <span class="span-data"><?php echo htmlspecialchars($pub['author'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($pub['journal'])): ?>
                                                    <p class="pub-info-line"><strong>Journal:</strong> <span class="span-data"><?php echo htmlspecialchars($pub['journal'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($pub['volume'])): ?>
                                                    <p class="pub-info-line"><strong>Volume:</strong> <span class="span-data"><?php echo htmlspecialchars($pub['volume'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($pub['page'])): ?>
                                                    <p class="pub-info-line"><strong>Page:</strong> <span class="span-data"><?php echo htmlspecialchars($pub['page'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                                                <?php endif; ?>
                                                
                                                <?php 
                                                $linkUrl = !empty($pub['doilink']) ? $pub['doilink'] : (!empty($pub['doi']) ? (strpos($pub['doi'], 'http') === 0 ? $pub['doi'] : 'https://doi.org/' . $pub['doi']) : '');
                                                if (!empty($linkUrl)): 
                                                ?>
                                                    <p class="pub-info-line"><strong>DOI/Link:</strong> <a href="<?php echo htmlspecialchars($linkUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener"><?php echo htmlspecialchars(!empty($pub['doi']) ? $pub['doi'] : $linkUrl, ENT_QUOTES, 'UTF-8'); ?></a></p>
                                                <?php endif; ?>

                                                <?php if (!empty($pub['year'])): ?>
                                                    <p class="pub-info-line"><strong>Year:</strong> <span class="span-data"><?php echo htmlspecialchars($pub['year'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty(trim($facr['publication'] ?? ''))): ?>
                                <ul style="margin-top: 15px;">
                                    <?php
                                    $lines = explode("\n", $facr['publication']);
                                    foreach ($lines as $line) {
                                        $s = trim($line);
                                        if (!empty($s)) {
                                            echo '<li>' . htmlspecialchars($s, ENT_QUOTES, 'UTF-8') . '</li>';
                                        }
                                    }
                                    ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Journal Publications (journalpub table & faculty.journal text) -->
            <?php if (!empty($journalPublications) || !empty(trim($facr['journal'] ?? ''))): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingJournals">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseJournals" aria-expanded="false" aria-controls="collapseJournals">
                            <span class="acc-bar">Journal</span>
                        </button>
                    </h2>
                    <div id="collapseJournals" class="accordion-collapse collapse" aria-labelledby="headingJournals" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <?php if (!empty($journalPublications)): ?>
                                <div class="publication-entries">
                                    <?php foreach ($journalPublications as $pub): ?>
                                        <div class="pub-entry">
                                            <div class="pub-circle-icon">
                                                <svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                                            </div>
                                            <div class="pub-text-box">
                                                <div class="pub-title-bold"><?php echo htmlspecialchars($pub['title'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                
                                                <?php if (!empty($pub['author'])): ?>
                                                    <p class="pub-info-line"><strong>Author:</strong> <span class="span-data"><?php echo htmlspecialchars($pub['author'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($pub['journal'])): ?>
                                                    <p class="pub-info-line"><strong>Journal:</strong> <span class="span-data"><?php echo htmlspecialchars($pub['journal'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($pub['volume'])): ?>
                                                    <p class="pub-info-line"><strong>Volume:</strong> <span class="span-data"><?php echo htmlspecialchars($pub['volume'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($pub['page'])): ?>
                                                    <p class="pub-info-line"><strong>Page:</strong> <span class="span-data"><?php echo htmlspecialchars($pub['page'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                                                <?php endif; ?>
                                                
                                                <?php 
                                                $linkUrl = !empty($pub['doilink']) ? $pub['doilink'] : (!empty($pub['doi']) ? (strpos($pub['doi'], 'http') === 0 ? $pub['doi'] : 'https://doi.org/' . $pub['doi']) : '');
                                                if (!empty($linkUrl)): 
                                                ?>
                                                    <p class="pub-info-line"><strong>DOI/Link:</strong> <a href="<?php echo htmlspecialchars($linkUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener"><?php echo htmlspecialchars(!empty($pub['doi']) ? $pub['doi'] : $linkUrl, ENT_QUOTES, 'UTF-8'); ?></a></p>
                                                <?php endif; ?>

                                                <?php if (!empty($pub['year'])): ?>
                                                    <p class="pub-info-line"><strong>Year:</strong> <span class="span-data"><?php echo htmlspecialchars($pub['year'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty(trim($facr['journal'] ?? ''))): ?>
                                <ul style="margin-top: 15px;">
                                    <?php
                                    $lines = explode("\n", $facr['journal']);
                                    foreach ($lines as $line) {
                                        $s = trim($line);
                                        if (!empty($s)) {
                                            echo '<li>' . htmlspecialchars($s, ENT_QUOTES, 'UTF-8') . '</li>';
                                        }
                                    }
                                    ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Conference Proceedings (confrncepub table & faculty.conference text) -->
            <?php if (!empty($confPublications) || !empty(trim($facr['conference'] ?? ''))): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingConferences">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseConferences" aria-expanded="false" aria-controls="collapseConferences">
                            <span class="acc-bar">Conference</span>
                        </button>
                    </h2>
                    <div id="collapseConferences" class="accordion-collapse collapse" aria-labelledby="headingConferences" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <?php if (!empty($confPublications)): ?>
                                <div class="publication-entries">
                                    <?php foreach ($confPublications as $pub): ?>
                                        <div class="pub-entry">
                                            <div class="pub-circle-icon">
                                                <svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                                            </div>
                                            <div class="pub-text-box">
                                                <div class="pub-title-bold"><?php echo htmlspecialchars($pub['title'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                
                                                <?php if (!empty($pub['author'])): ?>
                                                    <p class="pub-info-line"><strong>Author:</strong> <span class="span-data"><?php echo htmlspecialchars($pub['author'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($pub['conference'])): ?>
                                                    <p class="pub-info-line"><strong>Conference:</strong> <span class="span-data"><?php echo htmlspecialchars($pub['conference'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($pub['volume'])): ?>
                                                    <p class="pub-info-line"><strong>Volume:</strong> <span class="span-data"><?php echo htmlspecialchars($pub['volume'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($pub['page'])): ?>
                                                    <p class="pub-info-line"><strong>Page:</strong> <span class="span-data"><?php echo htmlspecialchars($pub['page'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                                                <?php endif; ?>
                                                
                                                <?php 
                                                $linkUrl = !empty($pub['doilink']) ? $pub['doilink'] : (!empty($pub['doi']) ? (strpos($pub['doi'], 'http') === 0 ? $pub['doi'] : 'https://doi.org/' . $pub['doi']) : '');
                                                if (!empty($linkUrl)): 
                                                ?>
                                                    <p class="pub-info-line"><strong>DOI/Link:</strong> <a href="<?php echo htmlspecialchars($linkUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener"><?php echo htmlspecialchars(!empty($pub['doi']) ? $pub['doi'] : $linkUrl, ENT_QUOTES, 'UTF-8'); ?></a></p>
                                                <?php endif; ?>

                                                <?php if (!empty($pub['year'])): ?>
                                                    <p class="pub-info-line"><strong>Year:</strong> <span class="span-data"><?php echo htmlspecialchars($pub['year'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty(trim($facr['conference'] ?? ''))): ?>
                                <ul style="margin-top: 15px;">
                                    <?php
                                    $lines = explode("\n", $facr['conference']);
                                    foreach ($lines as $line) {
                                        $s = trim($line);
                                        if (!empty($s)) {
                                            echo '<li>' . htmlspecialchars($s, ENT_QUOTES, 'UTF-8') . '</li>';
                                        }
                                    }
                                    ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Books Authored -->
            <?php if (!empty($facr['books'])): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingBooks">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBooks" aria-expanded="false" aria-controls="collapseBooks">
                            <span class="acc-bar">Books Authored</span>
                        </button>
                    </h2>
                    <div id="collapseBooks" class="accordion-collapse collapse" aria-labelledby="headingBooks" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <ul>
                                <?php
                                $lines = explode("\n", $facr['books']);
                                foreach ($lines as $line) {
                                    $s = trim($line);
                                    if (!empty($s)) {
                                        echo '<li>' . htmlspecialchars($s, ENT_QUOTES, 'UTF-8') . '</li>';
                                    }
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Patents -->
            <?php if (!empty($facr['patent'])): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingPatents">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePatents" aria-expanded="false" aria-controls="collapsePatents">
                            <span class="acc-bar">Patents</span>
                        </button>
                    </h2>
                    <div id="collapsePatents" class="accordion-collapse collapse" aria-labelledby="headingPatents" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <ul>
                                <?php
                                $lines = explode("\n", $facr['patent']);
                                foreach ($lines as $line) {
                                    $s = trim($line);
                                    if (!empty($s)) {
                                        echo '<li>' . htmlspecialchars($s, ENT_QUOTES, 'UTF-8') . '</li>';
                                    }
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Other Information -->
            <?php if (!empty($facr['otherinfo'])): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingOther">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOther" aria-expanded="false" aria-controls="collapseOther">
                            <span class="acc-bar">Other Information</span>
                        </button>
                    </h2>
                    <div id="collapseOther" class="accordion-collapse collapse" aria-labelledby="headingOther" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <ul>
                                <?php
                                $lines = explode("\n", $facr['otherinfo']);
                                foreach ($lines as $line) {
                                    $s = trim($line);
                                    if (!empty($s)) {
                                        echo '<li>' . htmlspecialchars($s, ENT_QUOTES, 'UTF-8') . '</li>';
                                    }
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php include('footer.php'); ?>
