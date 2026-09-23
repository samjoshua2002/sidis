<?php
require_once __DIR__ . '/config.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIDiS</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="images/iitm-logo.png">
    <!-- Bootstrap -->
    <link href="bootstrap/bootstrap.min.css" rel="stylesheet">

    <!-- Inter Font -->
    <link href="fonts/inter.css" rel="stylesheet">
    <!-- Lato Font -->
    <link href="fonts/lato.css" rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" href="owlcarousel/owl.carousel.min.css">
    <link rel="stylesheet" href="owlcarousel/owl.theme.default.min.css">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="owlcarousel/owl.carousel.min.js"></script>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <!-- HEADER -->
    <nav class="navbar navbar-expand-lg bg-white shadow-sm fixed-top">
        <div class="container">
            <div class="row" style="    border-bottom: 1px solid;
    min-width: 100%;
    padding-bottom: 10px;
"> <!-- Logo -->
                <a class="navbar-brand d-flex align-items-center" href="index.php">
                    <img src="images/logo.svg" alt="logo" height="60" class="logo-header">

                </a>
            </div>

            <div class="row" style="padding-top: 10px;"> <!-- Logo -->
                <!-- Hamburger -->
                <button class="navbar-toggler border-0" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#mobileMenu">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Desktop Menu -->
                <div class="collapse navbar-collapse">
                    <ul class="navbar-nav ms-auto">

                        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>

                        <!-- About -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown">About</a>
                            <ul class="dropdown-menu">
                                <!-- <li><a class="dropdown-item" href="about.php">About Us</a></li> -->
                                <!-- <li><a class="dropdown-item" href="clusters.php">Clusters</a></li> -->
                                <li><a class="dropdown-item" href="vision.php">Vision</a></li>
                                <li><a class="dropdown-item" href="committee.php">Organizational<br> Structure</a></li>
                                <!-- <li><a class="dropdown-item">Facilities</a></li>
                            <li><a class="dropdown-item">News</a></li> -->
                            </ul>
                        </li>



                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                People
                            </a>

                            <ul class="dropdown-menu">

                                <!-- Faculty -->
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item d-flex justify-content-between" href="faculty.php">
                                        Faculty
                                        <!-- <span>&#9656;</span>                                    -->
                                    </a>

                                    <!-- <ul class="submenu list-unstyled">
                                        <li><a class="dropdown-item" href="#">Core Faculty</a></li>
                                        <li><a class="dropdown-item" href="#">Affiliated Faculty</a>
                                        </li>
                                    </ul> -->
                                </li>

                                <!-- Students -->
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item d-flex justify-content-between" href="javascript:void(0)">
                                        Students
                                        <span>&#9656;</span> </a>

                                    <ul class="submenu list-unstyled">
                                        <li><a class="dropdown-item" href="ms.php">M.S Students</a></li>
                                        <li><a class="dropdown-item" href="phd.php">Ph.D Scholars</a></li>
                                        <li><a class="dropdown-item" href="mtech.php">M.Tech Students</a></li>
                                        <!-- <li><a class="dropdown-item" href="#">IDDD & I2MP</a></li> -->
                                    </ul>
                                </li>

                                <!-- Staff -->
                                <li>
                                    <a class="dropdown-item" href="staff.php">Staff</a>
                                </li>

                            </ul>
                        </li>


                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Academic Programmes</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="bcyber_iitm.html"
                                        target="_blank">Bachelor of Cybersecurity</a></li>
                                <!-- <li><a class="dropdown-item" href="#">Postgraduate Programmes</a></li>
                                <li><a class="dropdown-item" href="#">Ph.D Programme</a></li> -->
                                <li><a class="dropdown-item" href="iddd.php">Interdisciplinary Dual Degree (IDDD)</a>
                                </li>
                                <li><a class="dropdown-item" href="i2mp.php">International Interdisciplinary Masters
                                        Program (I2MP)</a></li>
                                <li><a class="dropdown-item" href="jmp.php">Joint Masters Program (JMP)</a></li>
                                <li><a class="dropdown-item" href="ms-phd.php">M.S/Ph.D </a></li>
                                <li><a class="dropdown-item" href="btech.php">B. Tech. Minor Programmes</a></li>
                                <li><a class="dropdown-item"
                                        href="https://drive.google.com/file/d/12mCyBez1RxiahNX1nOwi5YUqMaQY5-pl/view"
                                        target="_blank">Convocation</a></li>


                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Admissions</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">How to Apply</a></li>
                                <li><a class="dropdown-item" href="#">Important Dates</a></li>
                            </ul>
                        </li>

                        <!-- <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Research</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="clusters.php">Clusters</a></li>
                                <li><a class="dropdown-item" href="#">Consulting Projects</a></li>
                                <li><a class="dropdown-item" href="#">Sponsored Projects</a></li>
                                <li><a class="dropdown-item" href="#">Publications</a></li>
                                <li><a class="dropdown-item" href="#">Patents</a></li>
                            </ul>
                        </li> -->
                        <li class="nav-item"><a class="nav-link" href="clusters.php">Clusters</a></li>

                        <!-- <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Facilities</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Laboratories</a></li>
                                <li><a class="dropdown-item" href="#">National Testing & Research Facilities</a></li>
                            </ul>
                        </li> -->

                        <!-- <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown">News & Events</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">News</a></li>
                                <li><a class="dropdown-item" href="#">Achievements</a></li>
                                <li><a class="dropdown-item" href="#">Gallery</a></li>
                            </ul>
                        </li> -->

                        <!-- <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Collaborations</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Industry Collaborations</a></li>
                                <li><a class="dropdown-item" href="#">Academic Collaborations</a></li>
                                <li><a class="dropdown-item" href="#">State & Union Governments</a></li>
                                <li><a class="dropdown-item" href="#">International Collaborations</a></li>
                            </ul>
                        </li> -->

                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li> 

                    </ul>
                </div>
            </div>
        </div>
    </nav>


    <!-- MOBILE OFFCANVAS -->
    <!-- MOBILE OFFCANVAS -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="mobileMenu">
        <div class="offcanvas-header">
            <h5 class="fw-semibold">Menu</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>

        <div class="offcanvas-body">
            <ul class="list-unstyled">

                <!-- Home -->
                <li><a href="index.php" class="mobile-link">Home</a></li>

                <!-- About -->
                <li>
                    <a class="mobile-link" data-bs-toggle="collapse" href="#aboutMenu">
                        About <span class="mobile-arrow">&#9662;</span>
                    </a>

                    <div class="collapse" id="aboutMenu">
                        <!-- <a href="about.php" class="mobile-sub">About Us</a> -->
                        <!-- <a href="clusters.php" class="mobile-sub">Clusters</a> -->
                        <a href="vision.php" class="mobile-sub">Vision</a>
                        <a href="committee.php" class="mobile-sub">Organizational Structure</a>
                    </div>
                </li>

                <!-- People -->
                <li>
                    <a class="mobile-link" data-bs-toggle="collapse" href="#peopleMenu">
                        People <span class="mobile-arrow">&#9662;</span>
                    </a>

                    <div class="collapse" id="peopleMenu">
                        <a href="faculty.php" class="mobile-sub">Faculty</a>
                        <!-- Faculty -->
                        <!-- <a class="mobile-sub" data-bs-toggle="collapse" href="#facultyMenu">
                            Faculty <span class="mobile-arrow">&#9662;</span>
                        </a>

                        <div class="collapse ps-3" id="facultyMenu">
                            <a href="#" class="mobile-sub">Core Faculty</a>
                            <a href="#" class="mobile-sub">Affiliated Faculty</a>
                        </div> -->

                        <!-- Students -->
                        <a class="mobile-sub" data-bs-toggle="collapse" href="#studentsMenu">
                            Students <span class="mobile-arrow">&#9662;</span>
                        </a>

                        <div class="collapse ps-3" id="studentsMenu">
                            <a href="ms.php" class="mobile-sub">M.S Students</a>
                            <a href="phd.php" class="mobile-sub">Ph.D Scholars</a>
                            <a href="mtech.php" class="mobile-sub">M.Tech Students</a>
                            <!-- <a href="#" class="mobile-sub">IDDD & I2MP</a> -->
                        </div>

                        <a href="staff.php" class="mobile-sub">Staff</a>

                    </div>
                </li>

                <!-- Academic Programmes -->
                <li>
                    <a class="mobile-link" data-bs-toggle="collapse" href="#academicMenu">
                        Academic Programmes <span class="mobile-arrow">&#9662;</span>
                    </a>

                    <div class="collapse" id="academicMenu">
                        <a href="bcyber_iitm.html" class="mobile-sub"
                            target="_blank">Bachelor of Cybersecurity</a>
                        <!-- <a href="#" class="mobile-sub">Postgraduate Programmes</a>
                        <a href="#" class="mobile-sub">Ph.D Programme</a> -->
                        <a href="iddd.php" class="mobile-sub">Interdisciplinary Dual Degree (IDDD)</a>
                        <a href="i2mp.php" class="mobile-sub">International Interdisciplinary Masters Program (I2MP)</a>
                        <a href="jmp.php" class="mobile-sub">Joint Masters Program (JMP)</a>
                        <a href="ms-phd.php" class="mobile-sub">M.S/Ph.D </a>
                        <a href="btech.php" class="mobile-sub">B. Tech. Minor Programmes</a>
                        <a href="https://drive.google.com/file/d/12mCyBez1RxiahNX1nOwi5YUqMaQY5-pl/view"
                            class="mobile-sub" target="_blank">Convocation</a>
                    </div>
                </li>

                <!-- Admissions -->
                <li>
                    <a class="mobile-link" data-bs-toggle="collapse" href="#admissionMenu">
                        Admissions <span class="mobile-arrow">&#9662;</span>
                    </a>

                    <div class="collapse" id="admissionMenu">
                        <a href="#" class="mobile-sub">How to Apply</a>
                        <a href="#" class="mobile-sub">Important Dates</a>
                    </div>
                </li>

                <li>
                    <a href="clusters.php" class="mobile-link">Clusters</a>
                </li>


                <!-- Research -->
                <!-- <li>
                    <a class="mobile-link" data-bs-toggle="collapse" href="#researchMenu">
                        Research <span class="mobile-arrow">&#9662;</span>
                    </a>

                    <div class="collapse" id="researchMenu">
                        <a href="#" class="mobile-sub">Research Clusters</a>
                        <a href="#" class="mobile-sub">Consulting Projects</a>
                        <a href="#" class="mobile-sub">Sponsored Projects</a>
                        <a href="#" class="mobile-sub">Publications</a>
                        <a href="#" class="mobile-sub">Patents</a>
                    </div>
                </li> -->

                <!-- Facilities -->
                <!-- <li>
                    <a class="mobile-link" data-bs-toggle="collapse" href="#facilityMenu">
                        Facilities <span class="mobile-arrow">&#9662;</span>
                    </a>

                    <div class="collapse" id="facilityMenu">
                        <a href="#" class="mobile-sub">Laboratories</a>
                        <a href="#" class="mobile-sub">National Testing & Research Facilities</a>
                    </div>
                </li> -->

                <!-- News & Events -->
                <!-- <li>
                    <a class="mobile-link" data-bs-toggle="collapse" href="#newsMenu">
                        News & Events <span class="mobile-arrow">&#9662;</span>
                    </a>

                    <div class="collapse" id="newsMenu">
                        <a href="#" class="mobile-sub">News</a>
                        <a href="#" class="mobile-sub">Achievements</a>
                        <a href="#" class="mobile-sub">Gallery</a>
                    </div>
                </li> -->

                <!-- Collaborations -->
                <!-- <li>
                    <a class="mobile-link" data-bs-toggle="collapse" href="#collabMenu">
                        Collaborations <span class="mobile-arrow">&#9662;</span>
                    </a>

                    <div class="collapse" id="collabMenu">
                        <a href="#" class="mobile-sub">Industry Collaborations</a>
                        <a href="#" class="mobile-sub">Academic Collaborations</a>
                        <a href="#" class="mobile-sub">State & Union Governments</a>
                        <a href="#" class="mobile-sub">International Collaborations</a>
                    </div>
                </li> -->

                <!-- Contact -->
                <!-- <li>
                    <a href="#" class="mobile-link">Contact</a>
                </li> -->

            </ul>
        </div>
    </div>

    <style>
        .mobile-arrow {
            margin-left: 5px;
        }
    </style>