<?php
include 'header.php';

$validTypes = ['all', 'core', 'associate', 'adjunct', 'pop'];
$typeParam = isset($_GET['type']) ? strtolower(trim($_GET['type'])) : 'all';
$facultyType = in_array($typeParam, $validTypes, true) ? $typeParam : 'all';

$heroTitles = [
    'all'       => 'Faculty',
    'core'      => 'Core Faculty',
    'associate' => 'Associate Faculty',
    'adjunct'   => 'Adjunct / Visiting Faculty',
    'pop'       => 'Professor of Practice (PoP)',
];

$currentTitle = $heroTitles[$facultyType] ?? 'Faculty';
?>

<!-- HERO SECTION -->
<section class="hero">
    <img src="images/about-banner.png" class="hero-img" style="height: auto;">

    <div class="container">
        <div class="hero-content about-section">
            <h2><?= htmlspecialchars($currentTitle) ?></h2>
        </div>
    </div>
</section>

<!-- SEARCH BAR AT CENTER -->
<div class="container live-search-container">
    <div class="live-search-wrapper">
        <i class="fa-solid fa-magnifying-glass search-icon"></i>
        <input 
            type="text" 
            id="facultySearchInput" 
            class="live-search-input" 
            placeholder="Search by name, department, designation..." 
            autocomplete="off"
            aria-label="Search faculty"
        >
        <button type="button" id="facultySearchClear" class="search-clear-btn" aria-label="Clear search" style="display: none;">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    <div id="facultyGlobalNoResult" class="live-search-no-result" style="display: none;">
        <i class="fa-solid fa-magnifying-glass" style="font-size: 26px; margin-bottom: 10px; display: block; color: #9CA3AF;"></i>
        No faculty members found matching "<span id="searchQueryText"></span>".
    </div>
</div>

<style>
    td>p>a {
        color: #000;
    }

    @media (max-width: 768px) {
        .hero-content h2 {
            font-size: 22px;
        }
    }

    @media (max-width: 768px) {
        .hero-img {
            height: 150px !important;
        }
    }

    @media (max-width: 768px) {
        .hero-content {
            top: 70% !important;
            background-color: #1F3C44;
            padding: 5px 20px;
            left: 0px;
        }
    }




    .phd-table-section {
        padding: 40px 0;
    }

    .phd-table {
        width: 100%;
        border-collapse: collapse;
    }

    .phd-table thead tr {
        background-color: #1F3C44;
    }

    .phd-table thead th {
        padding: 16px 18px;
        border-right: 1px solid #fff;
    }

    .phd-table thead th:last-child {
        border-right: none;
    }

    .phd-table thead th p {
        color: #fff;
        margin: 0;
    }

    .phd-table tbody tr:nth-child(even) {
        background-color: #F4E796;
    }

    .phd-table tbody td {
        padding: 25px 18px;
        border-right: 2px solid #fff;
        vertical-align: top;
    }

    .phd-table tbody td p {
        margin: 0;
    }

    .hero-content {
        top: 35%;
        background-color: #1F3C44;
        padding: 5px 20px;
    }

    .hero-content h2 {
        color: #fff;
    }

    /* Section Headings */
    .faculty-heading {
        font-family: 'Lato', sans-serif;
        font-size: 26px;
        color: #184C74;
        font-weight: 600;
        margin: 0 0 25px 0;
        line-height: 1.3;
        border: none;
        text-decoration: none;
        text-align: left;
    }

    .clusters-side-heading {
        font-family: 'Lato', sans-serif;
        font-size: 26px;
        color: #184C74;
        font-weight: 600;
        padding-bottom: 20px;
    }

    @media (max-width: 768px) {
        .faculty-heading {
            font-size: 22px;
            margin-bottom: 20px;
        }
    }

    .about-section p {
        font-family: 'Inter', sans-serif;
        margin: 5px;
        text-align: left;
    }

    .news-content h3 {
        display: inline-block;
        background-color: #1F3C44;
        color: #fff;
        font-size: 28px;
        margin-bottom: 20px;
    }

    .faculty-top-section {
        padding: 40px 0 25px;
        background: #ffffff;
    }

    .faculty-top-cards {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 25px;
    }

    .faculty-top-cards .faculty-card {
        width: 100%;
        display: flex;
        flex-direction: column;
    }

    @media (max-width: 1199px) {
        .faculty-top-cards {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 991px) {
        .faculty-top-cards {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 575px) {
        .faculty-top-cards {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .faculty-top-cards .faculty-card {
            max-width: 350px;
            margin: 0 auto;
        }
    }
</style>


<?php
if (!function_exists('getFacultyCardLink')) {
    function getFacultyCardLink(array $member): array {
        $id           = (int)($member['id'] ?? 0);
        $pageType     = strtolower(trim($member['page_type'] ?? ''));
        $personalPage = trim($member['personal_page'] ?? '');
        $isTopOrder   = !empty($member['top_order']) && (int)$member['top_order'] > 0;

        // TOP FACULTY (SIDIS Faculty: Core, Adjunct/Visiting, PoP)
        if ($isTopOrder) {
            // If set to personal external link or has personal URL configured
            if ($pageType === 'personal' || $pageType === 'link' || ($pageType !== 'inner' && !empty($personalPage) && $personalPage !== '#')) {
                if (preg_match('/^https?:\/\//i', $personalPage) || (!empty($personalPage) && $personalPage !== '#')) {
                    return ['url' => $personalPage, 'target' => '_blank'];
                }
                return ['url' => 'innerfaculty.php?id=' . $id, 'target' => '_self'];
            }
            // Default to inner faculty page
            return ['url' => 'innerfaculty.php?id=' . $id, 'target' => '_self'];
        }

        // ASSOCIATE FACULTY (Cluster Faculty / Department Affiliate) - NEVER inner page
        if (preg_match('/^https?:\/\//i', $personalPage)) {
            return ['url' => $personalPage, 'target' => '_blank'];
        }
        if (!empty($personalPage) && $personalPage !== '#') {
            return ['url' => $personalPage, 'target' => '_blank'];
        }

        return ['url' => '', 'target' => ''];
    }
}

if (!function_exists('renderFacultyCard')) {
    function renderFacultyCard(array $member) {
        $cardLink = getFacultyCardLink($member);
        ?>
        <?php if (!empty($cardLink['url'])): ?>
            <a href="<?php echo htmlspecialchars($cardLink['url'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo !empty($cardLink['target']) ? 'target="' . $cardLink['target'] . '"' : ''; ?> class="faculty-card">
        <?php else: ?>
            <div class="faculty-card">
        <?php endif; ?>

            <div class="faculty-image">
                <img src="images/faculty/<?php echo htmlspecialchars($member['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">
            </div>

            <div class="faculty-content">
                <h3 class="faculty-name">
                    <?php echo htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8'); ?>
                </h3>
                <p class="faculty-department">
                    <?php echo htmlspecialchars($member['department'], ENT_QUOTES, 'UTF-8'); ?>
                </p>
                <p class="faculty-designation">
                    <?php echo htmlspecialchars($member['designation'], ENT_QUOTES, 'UTF-8'); ?>
                </p>
                <?php if (!empty($member['email'])): ?>
                    <p class="faculty-email">
                        <i class="fa-regular fa-envelope"></i>
                        <?php echo htmlspecialchars($member['email'], ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                <?php endif; ?>
            </div>

        <?php if (!empty($cardLink['url'])): ?>
            </a>
        <?php else: ?>
            </div>
        <?php endif; ?>
        <?php
    }
}

// Fetch Top Faculty Categories (SIDIS Faculty)
$coreFaculty = [];
if ($facultyType === 'all' || $facultyType === 'core') {
    $coreFaculty = $pdo->query("
        SELECT * FROM faculty 
        WHERE status = 1 AND top_order = 1 
        ORDER BY id ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
}

$adjunctFaculty = [];
if ($facultyType === 'all' || $facultyType === 'adjunct') {
    $adjunctFaculty = $pdo->query("
        SELECT * FROM faculty 
        WHERE status = 1 AND top_order = 2 
        ORDER BY id ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
}

$popFaculty = [];
if ($facultyType === 'all' || $facultyType === 'pop') {
    $popFaculty = $pdo->query("
        SELECT * FROM faculty 
        WHERE status = 1 AND top_order = 3 
        ORDER BY id ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
}
?>

<?php if (!empty($coreFaculty)): ?>
<section class="faculty-top-section" id="core">
    <div class="container">
        <div class="faculty-top-cards">
            <?php foreach ($coreFaculty as $member) renderFacultyCard($member); ?>
        </div>
    </div>
</section>
<?php elseif ($facultyType === 'core'): ?>
<section class="faculty-top-section" id="core">
    <div class="container text-center py-5">
        <p style="color: #666; font-size: 18px;">No Core Faculty members found.</p>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($adjunctFaculty)): ?>
<section class="faculty-top-section" id="adjunct">
    <div class="container">
        <div class="faculty-top-cards">
            <?php foreach ($adjunctFaculty as $member) renderFacultyCard($member); ?>
        </div>
    </div>
</section>
<?php elseif ($facultyType === 'adjunct'): ?>
<section class="faculty-top-section" id="adjunct">
    <div class="container text-center py-5">
        <p style="color: #666; font-size: 18px;">No Adjunct / Visiting Faculty members found.</p>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($popFaculty)): ?>
<section class="faculty-top-section" id="pop">
    <div class="container">
        <div class="faculty-top-cards">
            <?php foreach ($popFaculty as $member) renderFacultyCard($member); ?>
        </div>
    </div>
</section>
<?php elseif ($facultyType === 'pop'): ?>
<section class="faculty-top-section" id="pop">
    <div class="container text-center py-5">
        <p style="color: #666; font-size: 18px;">No Professor of Practice members found.</p>
    </div>
</section>
<?php endif; ?>

<?php
$clusters = [];
$faculty = [];

if ($facultyType === 'all' || $facultyType === 'associate') {
    $clusterSql = "
        SELECT
            id,
            cluster_name
        FROM clusters
        WHERE status = 1
        ORDER BY id ASC
    ";

    $clusterStmt = $pdo->prepare($clusterSql);
    $clusterStmt->execute();
    $clusters = $clusterStmt->fetchAll();

    $facultySql = "
        SELECT
            f.id,
            f.cluster_id,
            f.name,
            f.image,
            f.department,
            f.designation,
            f.email,
            f.personal_page,
            f.top_order,
            f.page_type,
            f.about1,
            f.resrch
        FROM faculty f
        WHERE f.status = 1
            AND f.top_order = 0
            AND f.cluster_id IS NOT NULL
            AND f.cluster_id != ''
        ORDER BY
            f.name ASC
    ";

    $facultyStmt = $pdo->prepare($facultySql);
    $facultyStmt->execute();
    $faculty = $facultyStmt->fetchAll();
}
?>

<?php if ($facultyType === 'all' || $facultyType === 'associate'): ?>
<section class="faculty-section" id="associate">

    <div class="container">

        <div class="row">

            <div class="col-md-3 faculty-filter-column">

                <div class="faculty-filters">

                    <button type="button" class="faculty-filter active" data-cluster-filter="all">
                        All
                    </button>

                    <?php foreach ($clusters as $cluster): ?>

                        <button type="button" class="faculty-filter"
                            data-cluster-filter="<?php echo (int) $cluster['id']; ?>">
                            <?php echo htmlspecialchars(
                                $cluster['cluster_name'],
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>
                        </button>

                    <?php endforeach; ?>

                </div>

            </div>


            <div class="col-md-9 faculty-card-column">

                <div class="row" id="facultyContainer">

                    <?php foreach ($faculty as $member): 
                        $clusterIds = trim($member['cluster_id']);
                    ?>

                        <div class="col-lg-4 col-md-6 faculty-card-wrapper"
                            data-cluster-id="<?php echo htmlspecialchars(
                                $clusterIds,
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>">

                            <?php renderFacultyCard($member); ?>

                        </div>

                <?php endforeach; ?>

                <div id="facultyNoResult" class="faculty-no-result">
                    No faculty members found in this cluster.
                </div>

            </div>

        </div>

    </div>

    </div>

</section>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput     = document.getElementById('facultySearchInput');
    const clearBtn        = document.getElementById('facultySearchClear');
    const globalNoResult  = document.getElementById('facultyGlobalNoResult');
    const searchQueryText = document.getElementById('searchQueryText');
    const filterButtons   = document.querySelectorAll('.faculty-filter');
    const facultyCards    = document.querySelectorAll('.faculty-card-wrapper');
    const clusterNoResult = document.getElementById('facultyNoResult');
    const topSections     = document.querySelectorAll('.faculty-top-section');
    const associateSection= document.getElementById('associate');

    let currentCluster = 'all';

    function performSearch() {
        const query = searchInput ? searchInput.value.trim().toLowerCase() : '';

        // Show/hide clear button
        if (clearBtn) {
            clearBtn.style.display = query.length > 0 ? 'inline-flex' : 'none';
        }

        let totalVisible = 0;

        // 1. Top faculty cards (Core, Adjunct, PoP)
        topSections.forEach(function (section) {
            const cards = section.querySelectorAll('.faculty-card');
            let sectionVisible = 0;
            cards.forEach(function (card) {
                const text = (card.textContent || '').toLowerCase();
                if (!query || text.includes(query)) {
                    card.style.display = '';
                    sectionVisible++;
                    totalVisible++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (query && sectionVisible === 0) {
                section.style.display = 'none';
            } else {
                section.style.display = '';
            }
        });

        // 2. Associate faculty cards
        if (facultyCards.length > 0) {
            let associateVisible = 0;
            facultyCards.forEach(function (card) {
                const text = (card.textContent || '').toLowerCase();
                const facultyCluster = card.getAttribute('data-cluster-id') || '';
                const facultyClusters = facultyCluster
                    ? facultyCluster.split(',').map(function (id) { return id.trim(); })
                    : [];

                const matchesCluster = (currentCluster === 'all' || facultyClusters.includes(currentCluster));
                const matchesQuery   = (!query || text.includes(query));

                if (matchesCluster && matchesQuery) {
                    card.style.display = '';
                    associateVisible++;
                    totalVisible++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (clusterNoResult) {
                clusterNoResult.style.display = (associateVisible === 0 && !query) ? 'block' : 'none';
            }

            if (associateSection) {
                if (query && associateVisible === 0) {
                    associateSection.style.display = 'none';
                } else {
                    associateSection.style.display = '';
                }
            }
        }

        // 3. Global no result message
        if (globalNoResult) {
            if (query && totalVisible === 0) {
                if (searchQueryText) searchQueryText.textContent = searchInput ? searchInput.value.trim() : '';
                globalNoResult.style.display = 'block';
            } else {
                globalNoResult.style.display = 'none';
            }
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', performSearch);
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            if (searchInput) {
                searchInput.value = '';
                performSearch();
                searchInput.focus();
            }
        });
    }

    filterButtons.forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            currentCluster = this.getAttribute('data-cluster-filter') || 'all';

            filterButtons.forEach(function (btn) {
                btn.classList.remove('active');
            });
            this.classList.add('active');

            performSearch();
        });
    });
});
</script>



















<style>
    .faculty-section {
        padding: 40px 0 60px;
        background: #ffffff;
    }


    .faculty-filter-column {
        padding-right: 30px;
    }

    .faculty-card-column {
        padding-left: 15px;
    }

    .faculty-filters {
        width: 100%;
        overflow: hidden;
        border-radius: 5px;
        box-shadow: 0 5px 18px rgba(0, 0, 0, 0.18);
    }

    .faculty-filter {
        width: 100%;
        min-height: 62px;
        padding: 15px 20px;
        border: none;
        border-bottom: 1px solid rgba(255, 255, 255, 0.25);
        background: linear-gradient(to bottom,
                #1F3C44 0%,
                #184C74 100%);
        color: #ffffff;
        font-size: 16px;
        font-weight: 500;
        text-align: center;
        cursor: pointer;
        transition:
            background 0.35s ease,
            color 0.35s ease,
            box-shadow 0.35s ease,
            transform 0.35s ease;
    }

    .faculty-filter:last-child {
        border-bottom: none;
    }

    .faculty-filter:hover {
        background: linear-gradient(to bottom,
                #28505A 0%,
                #1F5B86 100%);
        color: #ffffff;
        box-shadow:
            inset 0 0 0 1px rgba(255, 255, 255, 0.12),
            0 4px 14px rgba(24, 76, 116, 0.25);
        transform: translateX(3px);
    }

    .faculty-filter.active {
        background: #ffffff;
        color: #184C74;
        box-shadow:
            inset 0 0 0 1px rgba(24, 76, 116, 0.12),
            0 3px 12px rgba(24, 76, 116, 0.12);
        transform: none;
    }

    .faculty-filter.active:hover {
        background: #ffffff;
        color: #184C74;
        box-shadow:
            inset 0 0 0 1px rgba(24, 76, 116, 0.16),
            0 4px 14px rgba(24, 76, 116, 0.16);
        transform: translateX(3px);
    }

    .faculty-card-wrapper {
        margin-bottom: 30px;
    }

    .faculty-card {
        display: flex;
        flex-direction: column;
        width: 100%;
        height: 100%;
        background: #ffffff;
        border: 1px solid #e3e3e3;
        text-decoration: none;
        color: inherit;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.10);
        transition:
            transform 0.3s ease,
            box-shadow 0.3s ease;
    }

    .faculty-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .faculty-image {
        width: 100%;
        height: 280px;
        background: #f5f5f5;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .faculty-image img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
        transition: transform 0.4s ease;
    }

    .faculty-card:hover .faculty-image img {
        transform: scale(1.03);
    }

    .faculty-content {
        padding: 20px 18px 22px;
        text-align: center;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
    }

    .faculty-name {
        margin: 0 0 8px;
        color: #184C74;
        font-size: 20px;
        font-weight: 600;
        line-height: 1.35;
    }

    .faculty-department {
        margin: 0 0 6px;
        color: #555555;
        font-size: 15px;
        font-weight: 500;
        line-height: 1.5;
    }

    .faculty-designation {
        margin: 0 0 12px;
        color: #666666;
        font-size: 15px;
        line-height: 1.5;
    }

    .faculty-email {
        margin: 0;
        color: #184C74;
        font-size: 14px;
        word-break: break-word;
    }

    .faculty-email i {
        margin-right: 6px;
    }

    .faculty-no-result {
        display: none;
        width: 100%;
        padding: 40px 20px;
        text-align: center;
        color: #666666;
        font-size: 17px;
    }

    @media (max-width: 991px) {

        .faculty-filter-column {
            padding-right: 15px;
            margin-bottom: 30px;
        }

        .faculty-card-column {
            padding-left: 15px;
        }

        .faculty-filters {
            display: flex;
            flex-wrap: wrap;
        }

        .faculty-filter {
            width: 50%;
            min-height: 55px;
        }

    }

    @media (max-width: 575px) {

        .faculty-section {
            padding: 45px 15px;
        }

        .faculty-filter {
            width: 100%;
        }

        .faculty-image {
            height: 300px;
        }

    }
</style>


<?php include 'footer.php'; ?>