<!doctype html>
<html lang="en">

<head>
  <title>Department of Aerospace Engineering</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <link href='https://fonts.googleapis.com/css?family=Roboto:400,100,300,700' rel='stylesheet' type='text/css'>
  <link rel="shortcut icon" type="image/x-icon" href="img/favicon.png">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

  <link rel="stylesheet" href="css2/style.css">
  <link rel="stylesheet" href="fonts2/icomoon/style.css">
  <link rel="stylesheet" href="css2/bootstrap-grid.min.css">
  <link rel="stylesheet" href="css2/bootstrap.min.css.map">

  <link href='fullcalendar/packages/core/main.css' rel='stylesheet' />
  <link href='fullcalendar/packages/daygrid/main.css' rel='stylesheet' />

  <!-- fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap"
    rel="stylesheet">


  <!-- boostrap - accordion -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
  <!-- Include Bootstrap CSS -->

  <!-- bootstrap carousel -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.0/css/bootstrap.min.css">
  <!-- carousel -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.0/js/bootstrap.min.js"></script>


  <!-- Add these lines in the head section of your HTML -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>


  <!-- carousel -->



  <!-- Include Owl Carousel CSS -->
  <!-- <link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css'> -->
  <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.carousel.min.css'>



  <!-- boostrap - accordion - script -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN"
    crossorigin="anonymous"></script>

  <script src="js/bootstrap.min.js"></script>
  




</head>

<body>

  <?php include('header.php'); ?>
  <!-- END nav -->
  <!-- content starts -->
  <!-- page content starts -->
  <!-- video section -->
  <section class="fullsize-video-bg">
    <div class="video-sec">
      <div id="carouselExampleIndicators1" class="carousel slide" data-ride="carousel">
        <div class="carousel-inner" style="height: auto !important;">
          <?php
          // Database connection
          require_once __DIR__ . '/vendor/autoload.php'; // Composer autoload (if using composer)
          $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
          $dotenv->load();

          $calendarId = $_ENV['GOOGLE_CALENDAR_ID'];
          $apiKey = $_ENV['GOOGLE_API_KEY'];

          // SQL query to fetch images and videos from the bannerimg table
          $sql = "SELECT id, image FROM bannerimg";
          $result = $conn->query($sql);

          if ($result && $result->num_rows > 0) {
            $isFirstItem = true; // To set the first item as active in the carousel
          
            while ($row = $result->fetch_assoc()) {
              $imagePath = 'img/' . $row["image"]; // Assuming 'img/' is the folder for media
              $activeClass = $isFirstItem ? 'active' : ''; // Set the first item as active
          
              // Get file extension to check if it's an image or a video
              $fileExtension = pathinfo($imagePath, PATHINFO_EXTENSION);

              if (in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif', 'bmp'])) {
                // If it's an image
                echo "
                            <div class='carousel-item $activeClass'>
                                <img src='$imagePath' alt='Slide' style='width:100vw;height: 60vh;object-fit: cover;' >                            </div>";
              } elseif (in_array(strtolower($fileExtension), ['mp4', 'webm'])) {
                // If it's a video
                echo "
                            <div class='carousel-item $activeClass'>
                                <video width='1600' height='450' autoplay muted loop>
                                    <source src='$imagePath' type='video/mp4'>
                                    <source src='$imagePath' type='video/webm'>
                                </video>
                            </div>";
              }

              // Set the first item as non-active after the first loop
              $isFirstItem = false;
            }
          } else {
            echo "No media found in the database.";
          }

          // Close the connection
          
          ?>
        </div>
        <a class="carousel-control-prev" href="#carouselExampleIndicators1" role="button" data-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#carouselExampleIndicators1" role="button" data-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="sr-only">Next</span>
        </a>

        <style>
          /* For tablets */
          @media (min-width: 768px) and (max-width: 991px) {
            .faculty-Achievement-image img {
              margin: 0 !important;
            }

            .faculty-Achievement-details h3 {
              margin-top: 0 !important;
            }

          }

          /* For tablets */
          @media (max-width: 480px) {
            .faculty-Achievement-image img {
              margin: 0 !important;
            }

            .faculty-Achievement-details h3 {
              margin-top: 0 !important;
            }

            .video-sec {
              height: 50vh !important;
            }

          }
        </style>
        <div class="latest-news" style="z-index:9;">
          <div class="card side-card mb-4">
            <div class="card-body">
              <h5 class="card-title">Upcoming Events</h5>
              <div class="ne-scroll">
                <div id="scroll-text"></div>
              </div>
              <div class="mt-5" style="text-align: center;">
                <a href="news-events.php?tab=Events" class="dep-btn" style="text-decoration: none; color: #fff;"
                  target="_blank pb-3">More Events</a>
              </div>
            </div>
          </div>
        </div>

        <script>
          const calendarId = '<?php echo $calendarId; ?>';
          const apiKey = '<?php echo $apiKey; ?>'; // replace with your API key
          const maxResults = 10; // show only 5 upcoming events

          async function fetchUpcomingEvents() {
            const url = `https://www.googleapis.com/calendar/v3/calendars/${calendarId}/events?key=${apiKey}&maxResults=${maxResults}&orderBy=startTime&singleEvents=true&timeMin=${new Date().toISOString()}`;

            try {
              const response = await fetch(url);
              const data = await response.json();
              const container = document.getElementById('scroll-text');
              container.innerHTML = '';

              if (data.items && data.items.length > 0) {
                data.items.forEach(event => {
                  const startDate = event.start.dateTime || event.start.date;
                  const endDate = event.end.dateTime || event.end.date;

                  const startTime = startDate.split('T')[1] || '09:00:00';
                  const endTime = endDate.split('T')[1] || '10:00:00';

                  // Convert to Google Calendar format
                  const startDateTime = new Date(startDate).toISOString().replace(/-|:|\.\d+/g, "");
                  const endDateTime = new Date(endDate).toISOString().replace(/-|:|\.\d+/g, "");

                  const calendarUrl = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent(event.summary)}&dates=${startDateTime}/${endDateTime}&details=${encodeURIComponent(event.description || '')}`;

                  // truncate title
                  let title = event.summary || 'Untitled Event';
                  if (title.length > 50) title = title.substring(0, 50) + '...';

                  container.innerHTML += `
                <div class="ne-box" style="display:flex; margin-bottom:-20px;">
                  <div class="ne-item" style="flex:1; overflow:hidden;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px;">
                      <a href="innerevents.php?id=${encodeURIComponent(event.id)}" style="color:#333; font-weight:500; text-decoration:none; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; display:block; flex:1;">
                        ${title}
                      </a>
                      <a href="${calendarUrl}" target="_blank" title="Add to Google Calendar" style="font-size:20px; color:#A00E09; margin-left:10px; flex-shrink:0;">
                        <i class="fa fa-calendar-plus-o"></i>
                      </a>
                    </div>
                    <span class="ne-date" style="color:#666; font-size:16px;">
                      <i class="fa fa-calendar"></i> ${new Date(startDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                    </span>
                  </div>
                </div>`;
                });
              } else {
                container.innerHTML = '<div class="alert alert-info">No upcoming events</div>';
              }
            } catch (error) {
              console.error(error);
              container.innerHTML = '<div class="alert alert-danger">Error fetching events</div>';
            }
          }

          fetchUpcomingEvents();
        </script>



      </div>
    </div>
  </section>


  <!-- video section end  -->
  <!-- ABOUT DEPARTMENT -->

  <?php
  // Fetch department content
  $query = "SELECT depcontent FROM departmentmesg WHERE id='1'";
  $result = mysqli_query($conn, $query);

  $DepContent = '';
  if ($result) {
    if (mysqli_num_rows($result) > 0) {
      $row = mysqli_fetch_assoc($result);
      $DepContent = trim($row['depcontent']);
    }
    mysqli_free_result($result);
  } else {
    echo 'Error executing the query: ' . mysqli_error($conn);
  }

  // Only show the section if depcontent is not empty
  if (!empty($DepContent)):
    ?>
    <section class="mech-content">
      <div class="container">
        <div class="row" style="padding: 20px 0 10px; ">
          <!-- abt-btn -->
          <div class="col-md-6 abt-btn ">
            <!-- wrapper -->
            <style>
              .wra {
                display: flex;
                flex-direction: column;
                gap: 20px;
                /* spacing between rows */
              }

              .button-box {
                display: flex;
                gap: 30px;
              }

              .button-box a {
                flex: 1;
                display: flex;
              }

              .button-box a button {
                flex: 1;
                height: 100%;
              }

              @media (max-width: 600px) {
                .carousel-item video {
                  width: 100%;
                  /* height: 200px; */
                  /* height: 200px; */
                  top: 250px;
                  /* position: fixed; */
                  height: 260px !important;

                }

                .side-card .ne-scroll {
                  height: 110px !important;
                }

                .button-box {
                  display: flex;
                  flex-direction: row;
                  gap: 10px;

                }

                .button-box a button {
                  font-size: 14px;
                  padding: 8px 5px;
                }

                .wra {
                  gap: 0px;
                }

                .abt-btn {
                  padding: 0 15px 15px;
                }

                .head-of-deprt {
                  font-size: 21px !important;
                  padding-bottom: 15px !important;
                  margin-bottom: 0px;
                }

                .head-content {
                  font-size: 16px !important;
                }

                .news-btn {
                  background: #A00E09;
                  border-radius: 5px;
                  color: #fff;
                  padding: 5px 10px 5px 10px;
                  margin-top: 0px;
                  border: none;
                }

              }
            </style>

<?php
// Fetch quick links from the database
$quickLinksQuery = "SELECT title, link FROM quicklinks ORDER BY id ASC";
$quickLinksResult = mysqli_query($conn, $quickLinksQuery);
$quickLinks = [];

if ($quickLinksResult && mysqli_num_rows($quickLinksResult) > 0) {
  while ($row = mysqli_fetch_assoc($quickLinksResult)) {

    // Don't show if title is empty
    if (!empty(trim($row['title'] ?? ''))) {
      $quickLinks[] = $row;
    }
  }
  mysqli_free_result($quickLinksResult);
}
?>

<div class="wra">
  <?php
  // Display quick links in groups of 2
  for ($i = 0; $i < count($quickLinks); $i += 2) {
    echo '<div class="button-box">';

    // First button
    $link = $quickLinks[$i]['link'] ?? '#';
    echo '<a href="' . htmlspecialchars($link) . '"><button type="button" class="btn-appl btn-15">'
      . htmlspecialchars($quickLinks[$i]['title']) . '</button></a>';

    // Second button (if exists)
    if (isset($quickLinks[$i + 1])) {
      $link = $quickLinks[$i + 1]['link'] ?? '#';
      echo '<a href="' . htmlspecialchars($link) . '"><button type="button" class="btn-appl btn-15">'
        . htmlspecialchars($quickLinks[$i + 1]['title']) . '</button></a>';
    }

    echo '</div>';
  }
  ?>
</div>


          

          </div>

          <div class="col-md-6 mx-auto hod-content" style="margin: auto;">
            <h2 class="head-of-deprt" style="padding-bottom: 20px;">
              <span class="hod-line" style="color: #a00;">|</span> About the Department
            </h2>
            <p class="head-content">
              <?php echo substr($DepContent, 0, 300); ?>...
            </p>
            <style>
              .head-content a,
              .head-content a:visited {
                color: #A00E09 !important;
              }
            </style>
            <button id="read-more-btn-1" class="news-btn">Read More</button>
          </div>
          <div id="myModal" class="modal">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">About the Department</h5>
                  <!-- Close button in the header -->
                  <span class="close1" style="cursor: pointer;color: #fff !important;font-size: 30px;">&times;</span>
                </div>
                <div class="modal-body-container">
                  <div class="modal-body">
                    <!-- Use Bootstrap grid system to create two columns -->
                    <div class="modal-body expop">
                      <div class="row">
                        <p class="head-content"><?php echo nl2br($DepContent); ?></p>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="modal-footer" style="justify-content: left !important;">
                  <p>The Department of Aerospace Engineering IIT Madras</p>
                  <!-- Close button in the footer -->
                  <!-- <button class="close-footer-btn" style="cursor: pointer;">Close</button> -->
                </div>
              </div>
            </div>
          </div>
          <script>
            // Get the modal
            var modal = document.getElementById("myModal");
            // Get the button that opens the modal
            var btn = document.getElementById("read-more-btn-1");
            // Get the close button in the header
            var span = document.getElementsByClassName("close1")[0];
            // Get the close button in the footer
            var closeFooterBtn1 = document.getElementsByClassName("close-footer-btn")[0];
            // Function to hide elements with class 'pixels'
            function hidePixels() {
              var pixelsElements = document.getElementsByClassName("pixels");
              for (var i = 0; i < pixelsElements.length; i++) {
                pixelsElements[i].style.display = "none";
              }
            }
            // Function to show elements with class 'pixels'
            function showPixels() {
              var pixelsElements = document.getElementsByClassName("pixels");
              for (var i = 0; i < pixelsElements.length; i++) {
                pixelsElements[i].style.display = "block";
              }
            }
            // When the user clicks the button, open the modal and hide elements with class 'pixels'
            btn.onclick = function () {
              modal.style.display = "block";
              hidePixels();
            }
            // When the user clicks on the header close button, close the modal and show elements with class 'pixels'
            span.onclick = function () {
              modal.style.display = "none";
              showPixels();
            }
            // When the user clicks on the footer close button, close the modal and show elements with class 'pixels'
            if (closeFooterBtn1) {
              closeFooterBtn1.onclick = function () {
                modal.style.display = "none";
                showPixels();
              }
            }
            // When the user clicks anywhere outside of the modal, close it and show elements with class 'pixels'
            window.onclick = function (event) {
              if (event.target == modal) {
                modal.style.display = "none";
                showPixels();
              }
            }
          </script>
        </div>
      </div>
    </section>
  <?php endif; ?>


  <!-- HEAD OF DEPARTMENT -->


  <?php
  $query = "SELECT hcontent, himg, hodname FROM hodcontent WHERE id='87'";
  $result = mysqli_query($conn, $query);

  if ($result) {
    if (mysqli_num_rows($result) > 0) {
      $row = mysqli_fetch_assoc($result);
      $HodContent = $row['hcontent'];
      $hodImage = $row['himg'];
      $HodName = $row['hodname'];

      // Only show the section if hcontent is not empty
      if (!empty($HodContent)) {
        ?>
        <div class="msg-frm-HOD">
          <div class="container">
            <div class="row justify-content-center hod-sec">
              <div class="col-md-7 mx-auto hod-content" style="margin: auto;">
                <h2 class="head-of-deprt" style="padding-bottom: 20px;">
                  <span class="hod-line" style="color: #a00;">|</span> Message from the Head of Department
                </h2>
                <p class="head-content"><?php echo substr($HodContent, 0, 700) . ' ...'; ?></p>
                <button id="read-more-btn-2" class="news-btn">Read More</button>
              </div>
              <style>
                .hod-sec {
                  padding: 10px 0px 50px 0px;
                }

                .carousel-control-next {
                  right: -70px !important;
                }

                .carousel-control-prev {
                  left: -70px !important;
                }

                .carousel-control-prev-icon,
                .carousel-control-next-icon {
                  filter: invert(0%);
                }

                @media screen and (min-width: 991px) {
                  .modal-dialog {
                    max-width: 70% !important;
                  }
                }

                @media screen and (max-width: 600px) {
                  .hod-sec {
                    padding: 0px 0px 20px 0px;
                  }

                  .hod-image {
                    width: 100%;
                    height: 288px;
                    object-fit: contain;

                  }
                }

                .modal {
                  display: none;
                  position: fixed;
                  z-index: 10;
                  left: 0;
                  top: 0;
                  width: 100%;
                  height: 100%;
                  background-color: rgb(0, 0, 0);
                  background-color: rgba(0, 0, 0, 0.8);
                }

                .modal {
                  overflow-y: scroll !important;
                }
              </style>
              <!-- Modal -->
              <div id="myModal-2" class="modal">
                <div class="modal-dialog" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="exampleModalLabel">Message from the Head of Department</h5>
                      <span class="close" style="cursor: pointer;color: #fff !important;font-size: 30px;">&times;</span>
                    </div>
                    <div class="modal-body-container">
                      <div class="modal-body">
                        <div class="modal-body expop">
                          <div class="row">
                            <p class="head-content"><?php echo nl2br($HodContent); ?></p>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="modal-footer" style="justify-content: left !important;">
                      <p>The Head, Department of Aerospace Engineering IIT Madras</p>
                    </div>
                  </div>
                </div>
              </div>

              <script>
                var modal2 = document.getElementById("myModal-2");
                var btn2 = document.getElementById("read-more-btn-2");
                var span2 = document.getElementsByClassName("close")[0];

                function hidePixels() {
                  var pixelsElements = document.getElementsByClassName("pixels");
                  for (var i = 0; i < pixelsElements.length; i++) {
                    pixelsElements[i].style.display = "none";
                  }
                }

                function showPixels() {
                  var pixelsElements = document.getElementsByClassName("pixels");
                  for (var i = 0; i < pixelsElements.length; i++) {
                    pixelsElements[i].style.display = "block";
                  }
                }

                btn2.onclick = function () {
                  modal2.style.display = "block";
                  hidePixels();
                }

                span2.onclick = function () {
                  modal2.style.display = "none";
                  showPixels();
                }

                window.onclick = function (event) {
                  if (event.target == modal2) {
                    modal2.style.display = "none";
                    showPixels();
                  }
                }
              </script>
              <style>
                @media (max-width: 992px) {
                  .custom-padding {
                    padding: 3rem;
                  }
                }

                @media (min-width: 993px) {
                  .custom-padding {
                    padding: 0px 0px 0px 109px;
                  }
                }
              </style>
              <div class="col-md-5 custom-padding">
                <div class="hod-image">
                  <img src="img/<?php echo $hodImage; ?>" alt="" style="width: 70%;">
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php
      } // End of if(!empty($HodContent))
    } // End of if(mysqli_num_rows($result) > 0)
  
    mysqli_free_result($result);
  } else {
    echo 'Error executing the query: ' . mysqli_error($conn);
  }
  ?>










  <!-- image section -->

  <?php
  // Fetch the background image for the counter section from the images table
  $counterBgImg = 'img/deer-bg.png'; // default fallback
  $imgQuery = "SELECT img FROM images WHERE section = 'counter' AND dropdown_names = 'Counter' LIMIT 1";
  $imgResult = $conn->query($imgQuery);
  if ($imgResult && $imgResult->num_rows > 0) {
    $imgRow = $imgResult->fetch_assoc();
    if (!empty($imgRow['img'])) {
      $counterBgImg = 'img/' . $imgRow['img'];
    }
  }
  ?>
  <!-- image section -->
  <div class="hero-deer"
    style="background: url('<?php echo $counterBgImg; ?>');background-position: bottom;background-size: cover;background-repeat: no-repeat;display: flex;padding: 40px 0 0 0;">
    <div class="container">
      <?php
      $stmt = $conn->prepare("SELECT DISTINCT cid FROM counter WHERE type='faculty'");
      $stmt->execute();
      $stmt->store_result(); // Store the result set
      $stmt->bind_result($cid); // Bind the result to a variable
      $cids = array(); // Array to store cids
      while ($stmt->fetch()) {
        $cids[] = $cid;
      }
      ?>

      <div class="row">

        <?php foreach ($cids as $key => $cid): ?>
          <div class="<?php echo $key < 5 ? 'col-md-5-custom counter-col' : 'col-md-3 counter-col'; ?>">
            <div class="home-counters">
              <div class="counter">
                <div class="counter-content">
                  <?php
                  $stmt = $conn->prepare("SELECT cheading, cvalue FROM counter WHERE type='faculty' AND cid=?");
                  $stmt->bind_param('i', $cid);
                  $stmt->execute();
                  $result = $stmt->get_result()->fetch_assoc();

                  // Fetch the values from the result
                
                  $cheading = $result['cheading'];
                  $cvalue = $result['cvalue'];
                  ?>

                  <span class="counter-value"><?php echo $cvalue; ?></span>
                  <?php
                  // apply mobile-only line breaks
                  $mobile_heading = $cheading;

                  // break only these two headings
                  if (stripos($cheading, 'Undergraduate') !== false) {
                    $mobile_heading = "Under<br>graduate<br> Students";
                  }
                  if (stripos($cheading, 'Postgraduate') !== false) {
                    $mobile_heading = "Post<br>graduate<br> Students";
                  }
                  ?>
                  <h3 class="counter-hdng"><?php echo $mobile_heading; ?></h3>

                </div>
              </div>
            </div>
          </div>

        <?php endforeach; ?>

      </div>

    </div>

  </div>
  </div>
  <style>
    /* Media query for screens below 600px */
    @media (max-width: 820px) {
      .box-column {
        flex: 0 0 50% !important;
        max-width: 50% !important;
      }

      .col-12-rsrch {
        margin: 15px 0px !important;
      }

      .card:hover .intro {
        height: 330px !important;
      }

    }

    @media (max-width: 600px) {
      .box-column {
        flex: 0 0 100% !important;
        max-width: 100% !important;

      }

      .box {
        text-align: center !important;
        height: 150px !important;
      }


    }

    /* Show breaks on mobile */
    @media(max-width: 768px) {
      .counter-hdng br {
        display: block;
      }
    }

    /* Hide breaks on desktop */
    @media(min-width: 769px) {
      .counter-hdng br {
        display: none;
      }
    }

    @media screen and (max-width: 600px) {
      .counter-col {
        width: 33.33% !important;
        /* forces 3 per row */
        padding: 5px;
        /* optional */
      }

      .counter-hdng:contains("Postgraduate Students") {
        display: block;
      }

      .counter-hdng:contains("Postgraduate Students")::before {
        content: "Post\A graduate\A students";
        white-space: pre-line;
      }

      .counter-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;

        /* allow wrapping & prevent overflow */
        white-space: normal;
        /* allow wrapping */
        word-break: normal;
        /* do NOT break words */
        overflow-wrap: normal;
        /* clean wrapping */

        padding: 5px;
      }

      .counter-value {
        font-size: 18px !important;
        margin-bottom: 4px !important;
        line-height: normal !important;
      }

      .counter-hdng {
        font-size: 12px !important;
        line-height: 16px !important;
        white-space: normal;
      }

      .home-counters {
        padding: 0px !important;
      }

      .counter .counter-content {
        padding: 0px !important;
      }
    }



    .col-md-5-custom {
      width: 20%;
    }

    .home-counters {
      padding: 10px;
    }

    @media (min-width: 768px) {
      .counter-4 {
        -ms-flex: 0 0 33.333333%;
        flex: 0 0 27.3333333% !important;
        max-width: 33.333333%;
      }

      @media only screen and (max-width: 600px) {
        .carousel-control-prev {
          top: -25% !important;
          left: -10% !important;
        }

        .carousel-control-next {
          top: -25% !important;
          right: -10% !important;
        }
      }

      @media only screen and (max-width: 900px) {
        .carousel-control-prev {
          top: -60% !important;
          left: -10% !important;
        }

        .carousel-control-next {
          top: -60% !important;
          right: -10% !important;
        }
      }

      @media only screen and (max-width: 550px) {
        .carousel-control-prev {
          top: -60% !important;
          left: -6% !important;
        }

        .carousel-control-next {
          top: -60% !important;
          right: -6% !important;
        }
      }
    }
  </style>
  <!-- youtube video -->



  <!-- Research Areas -->
  <style>
    .card {
      flex: 1;
      /* max-width: 300px; */
      margin: 20px;
      overflow: hidden;
      position: relative;
      background-color: #fff;
      transition: all 0.3s;
    }

    .intro {
      height: 80px;
      padding: 10px;
      text-align: center;
      background-color: rgba(27, 27, 27, 0);
      color: #fff;
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      transition: all 0.3s;

    }

    .aboutheading {
      margin: 10px;
      color: #FFF;
      font-family: Lato;
      font-size: 20px;
      font-style: normal;
      font-weight: 600;
      line-height: normal;
      letter-spacing: 1.6px;

      text-align: left;
      position: relative;
    }



    .card:hover .intro {
      height: 280px;
      bottom: 0;
      background: rgba(30, 30, 30, 0);
      text-align: left;
      background: linear-gradient(to bottom, #ffffff04, #000000fb);
      /* Changed to black */

      /* Adjust colors as needed */
    }



    .card:hover p {
      opacity: 2;
    }

    .animated-underline {
      display: inline-block;
      cursor: pointer;
      position: relative;
    }



    .card:hover hr.underline {
      width: 100%;
    }

    .overlay {
      position: relative;
      background-image: url('images/benefits.png') !important;
      /* Replace with the URL of your background image */
      background-size: cover;
      background-position: center;
      color: #fff;
      /* Text color on the overlay */
      padding: 100px 0;
      /* Adjust padding as needed */
      text-align: center;
    }


    .overlay::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      /* Changed color to black */
    }


    .content {
      z-index: 1;
    }

    .list-unstyled {
      color: #FFF;
      text-align: left;
    }

    .rsrch-cnt {
      font-size: 18px;
      font-family: 'lato', sans-serif;
      color: #fff;
      text-align: justify;
      padding: 10px;
      margin: 20px 0;
    }

    .knw-btn {
      display: inline-flex;
      align-items: center;
      background-color: transparent;
      color: #fff;
      border: none;
      border: none;
      cursor: pointer;
      padding: 0px 10px 10px 10px;
    }

    .knw-btn .arrow {
      margin-left: 5px;
      font-size: 28px;
      line-height: 1;
    }

 .resch-img {
    width: 402px;
    height: 450px;
    object-fit: cover;
}
    .about-page {
      padding-bottom: 40px;
    }


    @media only screen and (max-width: 600px) {

      .about-page {
        padding-bottom: 20px;
      }

      /* 3 per row, small spacing */
      .col-12-rsrch {
        width: 33.33% !important;
        padding: 4px !important;
        margin: 0 !important;
      }

      .card {
        margin: 5px 10px !important;
      }

   .resch-img {
        width: 100%;
        height: 150px;
        object-fit: cover;
    }
      /* Heading â€” 2 lines fixed */
      .aboutheading {
        font-size: 12px !important;
        letter-spacing: 0.5px !important;
        margin: 0px 0px 10px 0px !important;

        line-height: 1.2;
        height: calc(1.2em * 2);
        /* space for 2 lines */

        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
      }

      /* Content â†’ 1 line or 2? (you wrote 2) */
      .rsrch-cnt {
        font-size: 10px !important;
        margin: 8px 0 !important;
        padding: 0 !important;
        text-align: left !important;

        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
      }

      /* Remove row padding */
      .res {
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        display: flex;
        flex-wrap: wrap;
        margin: auto;
      }

      /* MOBILE ONLY â†’ Horizontal Scroll */

      /* Cards inside scroll */
      .scroll-mobile .col-12-rsrch {
        flex: 0 0 33.33%;
        /* shows ~1.2 cards */
      }

      .reserch-headng {
        padding: 0 !important;
        margin-bottom: 10px !important;
        font-size: 21px !important;
      }

      /* Smaller button */
      .knw-btn {
        padding: 0 !important;
        font-size: 11px !important;
      }

      .knw-btn .arrow {
        font-size: 14px !important;
      }

      /* Intro reduced */
      .intro {
        padding: 5px !important;
        height: 40px !important;
      }

      .card:hover .intro {
        height: 110px !important;
      }

    }
  </style>

  <!-- RESEARCH AREAS -->
  <?php
  // Fetch research area images from the database
  $researchImages = [];
  $imgQuery = "SELECT img, defaultimg FROM images WHERE section = 'researcharea' ORDER BY id ASC";
  $imgResult = $conn->query($imgQuery);
  if ($imgResult && $imgResult->num_rows > 0) {
    while ($imgRow = $imgResult->fetch_assoc()) {
      $researchImages[] = !empty($imgRow['img']) ? $imgRow['img'] : $imgRow['defaultimg'];
    }
  } else {
    // fallback if no images found
    $researchImages = ['1.png', '2.png', '3.png'];
  }
  ?>
  <section class="about-page">
    <div class="container">
      <div class="row" style="padding: 20px 10px 10px;">
        <h2 class="reserch-headng"><span class="resrch" style="color: #a00; ">|</span> Research Areas</h2>
      </div>
      <div class="row res scroll-mobile" style="padding: 20px 0;">
        <div class="col-lg-4 col-md-6 col-12-rsrch"> <!-- Make the column full width on screens below 280px width -->
          <div class="card">
            <img src="img/<?php echo htmlspecialchars($researchImages[0] ?? '1.png'); ?>" alt="img1" class="resch-img">
            <div class="intro">
              <h1 class="aboutheading">Aerodynamics, Flight Dynamics and Control</h1>


              <p class="rsrch-cnt">
                Theoretical, numerical and experimental studies are conducted on various aspects of fluid flow and
                flight mechanics...
              </p>
              <a href="aerospace-dynamics.php"><button type="button" class="knw-btn">Know More<span
                    class="arrow">&#x2197;</span></button></a>
            </div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 col-12-rsrch"> <!-- Make the column full width on screens below 280px width -->
          <div class="card">
            <img src="img/<?php echo htmlspecialchars($researchImages[1] ?? '2.png'); ?>" alt="img2" class="resch-img">
            <div class="intro">
              <h1 class="aboutheading">Aerospace Propulsion</h1>

              <p class="rsrch-cnt">
                Fundamental and applied theoretical, numerical and experimental research in the field of propulsion
                ranging from fuel mixture, combustion to development of...
              </p>
              <a href="aerospace-propulsion.php"><button type="button" class="knw-btn">Know More<span
                    class="arrow">&#x2197;</span></button></a>
            </div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 col-12-rsrch"> <!-- Make the column full width on screens below 280px width -->
          <div class="card">
            <img src="img/<?php echo htmlspecialchars($researchImages[2] ?? '3.png'); ?>" alt="img3" class="resch-img">
            <div class="intro">
              <h1 class="aboutheading">Aerospace Structures</h1>

              <p class="rsrch-cnt">
                Theoretical, numerical, experimental and computational research is conducted in the following broad
                areas of aerospace structures...
              </p>
              <a href="aerospace-structures.php"><button type="button" class="knw-btn">Know More<span
                    class="arrow">&#x2197;</span></button></a>
            </div>
          </div>
        </div>

      </div>
    </div>


  </section>

  <!-- ACADEMICS -->
  <div class="Resrch" id="research-groups" style="background-color: #FFF1F0;">
    <?php
    require_once __DIR__ . '/vendor/autoload.php';

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    // Access .env variables
    $db_host = $_ENV['DB_HOST'];
    $db_name = $_ENV['DB_NAME'];
    $db_username = $_ENV['DB_USERNAME'];
    $db_password = $_ENV['DB_PASSWORD'];

    // Connect to the database
    $connection = mysqli_connect($db_host, $db_username, $db_password, $db_name);

    // Check connection
    if ($connection->connect_error) {
      die("Connection failed: " . $connection->connect_error);
    }

    // Fetch data from academics table
    $sql = "SELECT acaicon, academictitle, academicduration , links FROM academics";
    $result = $connection->query($sql);

    if ($result->num_rows > 0) {
      $academics = $result->fetch_all(MYSQLI_ASSOC);
    } else {
      $academics = [];
    }

    $connection->close();
    ?>

    <div class="container" style="padding: 30px;">
      <div class="row" style="padding: 10px 0;">
        <h2 class="reserch-headng"><span class="resrch" style="color: #a00;">|</span> Academics</h2>
      </div>
      <div class="row justify-content-center">
        <?php foreach ($academics as $academic): ?>
          <?php if (!empty($academic['academictitle'])): ?>

            <div class="col-md-4 col-sm-6 box-column">

              <div class="box" style="text-align: left;">
                <div class="icon-ac">
                  <a href="<?= htmlspecialchars($academic['links']); ?>" style="all: unset; cursor: pointer;">

                    <img src="img/<?= htmlspecialchars($academic['acaicon']); ?>" alt="Icon">
                  </a>
                </div>
                <div class="content">
                  <h4>
                    <a href="<?= htmlspecialchars($academic['links']); ?>" style="all: unset; cursor: pointer;">
                      <?= htmlspecialchars($academic['academictitle']); ?>
                    </a>
                  </h4>

                  <p><?php echo htmlspecialchars($academic['academicduration']); ?></p>


                </div>
              </div>
            </div>

          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>



    <style>
      @media (max-width: 600px) {
        .box-column {
          flex: 0 0 50% !important;
          max-width: 50% !important;
        }

        .box {
          margin: 0px !important;
          padding: 10px !important;
          height: 150px;
        }

        .content h4 {
          font-size: 14px !important;
          margin-bottom: 10px;
        }

        .content p {
          font-size: 12px !important;
          color: #666;
        }

        .box .icon-ac {
          margin: 0 !important;
        }

        .icon-ac img {
          width: 20px;
          height: 20px;
        }


      }

      .box {
        height: 200px;
        /* Adjust height as needed */
        width: 100%;
        /* Ensure the box takes the full width of its column */
        display: flex;
        margin: 10px;
      }

      .box-column {
        padding: 10px;
        display: flex;
      }

      .box {
        background-color: white;
        padding: 20px;
        text-align: center;

      }



      .content h4 {
        font-size: 18px;
        margin-bottom: 10px;
      }

      .content p {
        font-size: 14px;
        color: #666;
      }

      @media (max-width: 768px) {
        .col-sm-6 {
          flex: 0 0 100%;
          max-width: 100%;
        }
      }
    </style>



  </div>
  <?php
  // Fetch all data
  $faculty_result = null;
  $student_result = null;
  $news_result = null;
  $active_columns = 0;

  // Faculty Achievement
  $query = "SELECT aid, facname, facdesig, fimg, fcontent, fdate
            FROM achievement
            WHERE facname != ''
            ORDER BY display_order ASC, aid ASC
            LIMIT 4";
  $faculty_result = mysqli_query($conn, $query);
  if ($faculty_result && mysqli_num_rows($faculty_result) > 0) {
    $active_columns++;
  }

  // Student Achievement
  $query = "SELECT stuid, snameid, scontent, simg, sdate
            FROM stuach
            WHERE snameid != ''
            ORDER BY display_order ASC, stuid ASC
            LIMIT 4";
  $student_result = mysqli_query($conn, $query);
  if ($student_result && mysqli_num_rows($student_result) > 0) {
    $active_columns++;
  }

  // News
  $sql = "SELECT id, title, date, badge, img
        FROM `news-events`
        WHERE display_on_homepage = 1 AND badge = 'News'
        ORDER BY display_order ASC, id ASC
        LIMIT 4";
  $news_result = $conn->query($sql);
  if ($news_result && $news_result->num_rows > 0) {
    $active_columns++;
  }

  // Determine column class based on active columns
  $col_class = 'col-md-4'; // Default for 3 columns
  if ($active_columns === 2) {
    $col_class = 'col-md-6';
  } elseif ($active_columns === 1) {
    $col_class = 'col-md-6 mx-auto';
  }
  ?>

  <div class="row equal-height-sections no-gutters">

    <!-- Faculty Achievement -->
    <?php if ($faculty_result && mysqli_num_rows($faculty_result) > 0): ?>
      <div class="<?= $col_class ?>">
        <div class="section-box faculty-section">
          <h3 class="section-title">
            <span class="title-line">|</span> Faculty Achievement
          </h3>

          <?php while ($row = mysqli_fetch_assoc($faculty_result)): ?>
            <a href="innerachievements.php?aid=<?= urlencode($row['aid']) ?>" class="section-link">
              <div class="section-item">
                <h4 class="section-content">
                  <?= html_entity_decode($row['fcontent']) ?>
                </h4>
                <p class="section-detail">
                  <?= htmlspecialchars($row['facname']) ?>
                </p>
                <div class="bar"></div>
              </div>
            </a>
          <?php endwhile; ?>

          <div class="section-more">
            <a href="achievements.php" class="btn-more btn-faculty">For More</a>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Student Achievement -->
    <?php if ($student_result && mysqli_num_rows($student_result) > 0): ?>
      <div class="<?= $col_class ?>" style="background-color: #FFF1F0;">
        <div class="section-box student-section">
          <h3 class="section-title">
            <span class="title-line">|</span> Student Achievement
          </h3>

          <?php while ($row = mysqli_fetch_assoc($student_result)): ?>
            <a href="innerstudach.php?stuid=<?= urlencode($row['stuid']) ?>" class="section-link">
              <div class="section-item">
                <h4 class="section-content">
                  <?= html_entity_decode($row['scontent']) ?>
                </h4>
                <p class="section-detail">
                  <?= htmlspecialchars($row['snameid']) ?>
                </p>
                <div class="bar"></div>
              </div>
            </a>
          <?php endwhile; ?>

          <div class="section-more">
            <a href="studentachievements.php" class="btn-more btn-faculty">For More</a>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- News -->
    <?php if ($news_result && $news_result->num_rows > 0): ?>
      <div class="<?= $col_class ?>">
        <div class="section-box news-section">
          <h3 class="section-title">
            <span class="title-line">|</span> News
          </h3>

          <?php while ($row = $news_result->fetch_assoc()): ?>
            <a href="innernews-events.php?id=<?= urlencode($row['id']) ?>" class="section-link">
              <div class="section-item">
                <h4 class="section-content">
                  <?= htmlspecialchars($row['title']) ?>
                </h4>
                <p class="section-detail news-date">
                  <?= date("d/m/Y", strtotime($row['date'])) ?>
                </p>
                <div class="bar"></div>
              </div>
            </a>
          <?php endwhile; ?>

          <div class="section-more">
            <a href="news-events.php" class="btn-more btn-faculty">For More</a>
          </div>
        </div>
      </div>
    <?php endif; ?>

  </div>

  <style>
    /* Remove column gaps */
    .no-gutters {
      margin-left: 0;
      margin-right: 0;
    }

    .no-gutters>[class*="col-"] {
      padding-left: 0;
      padding-right: 0;
    }

    /* Equal height layout */
    .equal-height-sections {
      display: flex;
      flex-wrap: wrap;
    }

    /* Section box */
    .section-box {
      height: 100%;
      display: flex;
      flex-direction: column;
      border: none;
    }

    /* Individual padding */
    .faculty-section {
      padding: 20px 25px 20px 15px;
    }

    .student-section {
      padding: 20px;
    }

    .news-section {
      padding: 20px 15px 20px 25px;
    }

    /* Title */
    .section-title {
      font-size: 24px;
      font-weight: 600;
      margin-bottom: 20px;
    }

    .title-line {
      color: #A00E09;
      margin-right: 8px;
    }

    /* Item */
    .section-item {
      padding: 12px 0;
      box-sizing: border-box;
    }

    /* Bar */
    .bar {
      height: 2px;
      /* consistent thickness */
      background: #A00E09;
      margin-top: 18px;
      width: 100%;
    }

    /* Click behavior */
    .section-link {
      text-decoration: none;
      color: inherit;
      display: block;
    }

    .section-link:hover .section-item {}

    /* Hover color for content */
    .section-link:hover .section-content {
      color: #A00E09 !important;
    }

    /* Content clamp */
    .section-content {
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
      font-size: 15px;
      color: black !important;
      line-height: 1.4;
      min-height: 42px;
      margin-bottom: 5px;
    }

    .section-detail {
      font-size: 14px;
      color: #666;
    }

    .news-date {
      font-weight: 500;
    }

    /* Button */
    .section-more {
      margin-top: auto;
      text-align: center;
      padding-top: 20px;
    }

    .btn-more {
      background: #A00E09;
      color: #fff;
      padding: 8px 25px;
      font-size: 14px;
      border-radius: 3px;
      text-decoration: none;
    }

    .btn-more:hover {
      background: #8c0c08;
      color: white;
    }

    /* Tablet */
    @media (min-width: 768px) and (max-width: 991px) {
      .section-title {
        font-size: 20px;
      }

      .section-content {
        font-size: 15px;
      }

      .section-detail {
        font-size: 14px;
      }
    }

    /* Mobile */
    @media (max-width: 767px) {
      .section-title {
        font-size: 18px;
      }

      .section-content {
        font-size: 14px;
      }

      .section-detail {
        font-size: 13px;
      }

      .btn-more {
        font-size: 13px;
        padding: 6px 18px;
      }
    }
  </style>






  <!-- footer -->

  <?php include('footer.php'); ?>




  <!-- calendar -->
  <script src='fullcalendar/packages/core/main.js'></script>
  <script src='fullcalendar/packages/interaction/main.js'></script>
  <script src='fullcalendar/packages/daygrid/main.js'></script>
  <script src='fullcalendar/packages/timegrid/main.js'></script>
  <script src='fullcalendar/packages/list/main.js'></script>



  <!-- Include Owl Carousel JS -->
  <script src="js/jquery-3.7.1.min.js"></script>
  <script src='https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.carousel.min.js'></script>
  <script src="script.js"></script>




  <!-- calendar script -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var calendarEl = document.getElementById('calendar');

      var calendar = new FullCalendar.Calendar(calendarEl, {
        plugins: ['interaction', 'dayGrid', 'timeGrid', 'list'],
        height: 'parent',
        header: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        defaultView: 'dayGridMonth',
        defaultDate: '2023-02-12',
        navLinks: true, // can click day/week names to navigate views
        editable: true,
        eventLimit: true, // allow "more" link when too many events
        events: [{
          title: 'All Day Event',
          start: '2020-02-01',
        },
        {
          title: 'Long Event',
          start: '2020-02-07',
          end: '2020-02-10'
        },
        {
          groupId: 999,
          title: 'Repeating Event',
          start: '2020-02-09T16:00:00'
        },
        {
          groupId: 999,
          title: 'Repeating Event',
          start: '2020-02-16T16:00:00'
        },
        {
          title: 'Conference',
          start: '2020-02-11',
          end: '2020-02-13'
        },
        {
          title: 'Meeting',
          start: '2020-02-12T10:30:00',
          end: '2020-02-12T12:30:00'
        },
        {
          title: 'Lunch',
          start: '2020-02-12T12:00:00'
        },
        {
          title: 'Meeting',
          start: '2020-02-12T14:30:00'
        },
        {
          title: 'IITM Hour',
          start: '2020-02-12T17:30:00'
        },
        {
          title: 'Dinner',
          start: '2020-02-12T20:00:00'
        },
        {
          title: 'G20 event',
          start: '2020-02-13T07:00:00'
        },
        {
          title: 'Click for Google',
          url: 'http://google.com/',
          start: '2023-02-28'
        }
        ]
      });

      calendar.render();
    });
  </script>

  <!-- calendar script End -->
</body>

<style>
  @media (min-width: 1200px) {
    .container {
      max-width: 95%;
    }
  }
</style>

</html>