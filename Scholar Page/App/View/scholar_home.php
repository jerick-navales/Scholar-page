<?php
session_start();

if (!isset($_SESSION["username"])) {
    header("location:../../index.php");
    exit();
}

$username = $_SESSION['username'];
if (strpos($username, '@') !== false) {
    $username = explode('@', $username)[0];
}


$possibleNames = preg_split('/[._]/', $username);

if (preg_match('/([a-z]+)([A-Z][a-z]+)/', $username)) {

    $firstName = preg_replace('/([a-z])([A-Z])/', '$1 $2', $username);
    $firstName = explode(' ', $firstName)[0];
} elseif (count($possibleNames) > 1) {

    $firstName = $possibleNames[0];
} else {

    $firstName = $username;
}


$firstName = ucfirst(strtolower($firstName));



$title = 'Scholar Home | SEDP HRMS';
$page = 'scholar home';
include('../../Core/Includes/header.php');
?>

<style>
    .custom-list-group-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .progress-bar {
        background-color: #003c3c;
    }
</style>
</head>

<body>

    <div class="container">
        <div class="row" style="margin-top: 5.5rem;">
            <div class="col-md-4">
                <div class="dashboard-card" style="background-color: #f0f9f9;">
                    <div class="icon-section-title">
                        <i style="font-weight: bold;" class="lni lni-graduation"></i>
                        <span style="font-weight: 700; font-size: 25px; font-family: 'Josefin Sans', sans-serif;">Good Day, <?php echo $firstName; ?>!</span>
                    </div>
                    <span style="padding-left: 2.3rem; font-weight: 600; font-size: 16px;">Welcome Back!</span>
                </div>

                <div class="dashboard-card" style="background-color: #f0f9f9;">
                    <h5 class="section-title">Academic Performance Overview</h5>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 80%;" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100">80%</div>
                    </div>
                    <p class="mt-2">GPA: 3.8 / 4.0</p>
                </div>

                <div class="dashboard-card" style="background-color: #f0f9f9;">
                    <h5 class="section-title">Calendar</h5>
                    <div id="calendar" class="event-calendar" style="background-color: #003c3c; color: #fff;"></div>
                </div>

            </div>

            <div class="col-md-8">
                <div class="dashboard-card" style="height: 95%; position: relative; background-color: #f0f9f9;">
                    <div class="custom-card-header bg-white shadow rounded border-dark">
                        <div class="admin-profile">
                            <img src="../../Public/Assets/Images/profile.jpg" alt="Admin Profile">
                            <div>
                                <strong style="font-size: 20px;">System Administrator</strong><br>
                                <?php
                                include("../../../Database/db.php");

                                $sql = "SELECT id, title, content, image, posted_date 
                                FROM announcement 
                                ORDER BY posted_date DESC 
                                LIMIT 1";

                                $result = $connection->query($sql);


                                if ($result->num_rows > 0) {

                                    $row = $result->fetch_assoc();
                                    $posted_date = $row['posted_date'];

                                    echo "
                                    <strong class='text-muted' id='postedTime' style='font-size: 10px;' data-time='" . htmlspecialchars($posted_date) . "'>Posted: <span id='timeAgo'></span></strong>
                                    <script>
                                        function timeAgo() {
                                            const postedDate = new Date(document.getElementById('postedTime').getAttribute('data-time'));
                                            const now = new Date();
                                            const interval = Math.floor((now - postedDate) / 1000); // Get time in seconds

                                            let timeAgo = '';
                                            if (interval < 60) {
                                                timeAgo = 'just now';
                                            } else if (interval < 3600) { // Less than an hour
                                                const minutes = Math.floor(interval / 60);
                                                timeAgo = minutes + ' minute' + (minutes > 1 ? 's' : '') + ' ago';
                                            } else if (interval < 86400) { // Less than a day
                                                const hours = Math.floor(interval / 3600);
                                                timeAgo = hours + ' hour' + (hours > 1 ? 's' : '') + ' ago';
                                            } else { // More than a day
                                                const days = Math.floor(interval / 86400);
                                                timeAgo = days + ' day' + (days > 1 ? 's' : '') + ' ago';
                                            }
                                            
                                            document.getElementById('timeAgo').innerText = timeAgo;
                                        }

                                        // Update the timeAgo on load and then every minute
                                        timeAgo();
                                        setInterval(timeAgo, 60000);
                                    </script>
                                    ";
                                } else {
                                    echo "<p>No announcements found.</p>";
                                }
                                ?>
                            </div>
                        </div>
                        <span class="floating-announcement">Announcement</span>
                    </div>
                    <div class="announcement shadow rounded p-4 my-3 bg-white">
                        <?php
                        $sql = "SELECT id, title, content, posted_date 
                                FROM announcement 
                                ORDER BY posted_date DESC 
                                LIMIT 1";

                        $result = $connection->query($sql);

                        if ($result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            $title = $row['title'];
                            $content = $row['content'];

                            echo "
                            <h1 class='fs-4 p-2 m-1 fw-bold '>" . htmlspecialchars($title) . "</h1>
                            <p class='m-2'>" . htmlspecialchars($content) . "</p>
                            <img src='../../Public/Assets/Images/announcement.png' alt='Announcement Image' class='mt-3' style='width: 100%; height: 280px; border-radius: 6px; '>
                            ";
                        } else {
                            echo "<p>No announcements found.</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <?php
        // Requirements and their corresponding tables
        $requirements = [
            "scholar_narrative_reports" => "Narrative Reports",
            "scholar_load_expenses" => "Load Expenses",
            "scholar_book_expenses" => "Book Expenses",
            "scholar_thesis_expenses" => "Thesis Expenses",
            "scholar_certificate_of_registration" => "Certificate of Registration",
            "scholar_certificate_of_grade" => "Certificate of Grade"
        ];

        // Get current month as a full name (e.g., "November")
        $currentMonth = date('F');

        // Function to check if there's data for the current month
        function hasDataForCurrentMonth($tableName, $connection, $currentMonth) {
            // Prepare SQL query to check if there's a record in the current month
            $query = "SELECT COUNT(*) as count FROM $tableName WHERE report_month = ?";
            
            // Debugging: Print query and current month (can be removed later)
            echo "<!-- Query: $query, Current Month: $currentMonth -->";
            
            $stmt = $connection->prepare($query);
            $stmt->bind_param("s", $currentMonth);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            // Debugging: Show result count (can be removed later)
            echo "<!-- Result Count for $tableName: {$result['count']} -->";

            return $result['count'] > 0;
        }

        // Function to determine if the month has changed
        function hasMonthChanged($previousMonth) {
            return date('F') !== $previousMonth;
        }

        // Store the last month checked (you could store this in a session or database)
        $previousMonth = $_SESSION['last_checked_month'] ?? date('F');

        // Check if the month has changed
        if (hasMonthChanged($previousMonth)) {
            // Reset the last checked month
            $_SESSION['last_checked_month'] = date('F');
        }

        ?>

        <div class="col-md-12 shadow rounded p-4 mb-4" style="background-color: #f0f9f9;">
            <div class="dashboard-card">
                <h5 class="section-title">Requirements Tracker</h5>
                <ul class="list-group">
                    <?php foreach ($requirements as $tableName => $label): ?>
                        <?php
                        // Check if data exists for the current month for this requirement
                        $hasData = hasDataForCurrentMonth($tableName, $connection, $currentMonth);
                        ?>

                        <li class="list-group-item custom-list-group-item">
                            <?php echo $label; ?>
                            <?php if ($hasData): ?>
                                <i class="fas fa-check-circle" style="color: #003c3c;"></i>
                            <?php else: ?>
                                <i class="fas fa-times-circle" style="color: #6A1E54;"></i>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="col-md-12 shadow rounded p-4 mb-4" style="background-color: #f0f9f9;">
            <h5 class="text-center" style="color: #003c3c; font-weight: bold; letter-spacing: 1px;">User Activity Over Time</h5>
            <canvas id="userActivityChart"></canvas>
        </div>

    </div>
    <div class="container-fluid bg-dark text-center text-light" style="padding: 10px 0;">
        <div class="footer-content" style="min-height: 100px; line-height: 30px;">
            <p class="mb-2">&copy; 2024 Your Organization. All Rights Reserved.</p>

            <ul class="list-inline mb-2">
                <li class="list-inline-item"><a href="https://sedp.ph/about-us/" class="text-light">About Us</a></li>
                <li class="list-inline-item"><a href="https://sedp.ph/services/" class="text-light">Services</a></li>
                <li class="list-inline-item"><a href="/privacy-policy" class="text-light">Privacy Policy</a></li>
                <li class="list-inline-item"><a href="/terms-of-service" class="text-light">Terms of Service</a></li>
            </ul>

            <p class="mb-2">Contact Us: <a href="mailto:mfi@sedp.ph " class="text-light">simbag_sedp@yahoo.com</a></p>

            <div class="social-media-links mb-2">
                <a href="https://web.facebook.com/sedp.ph" target="_blank" class="mx-2 text-light"><i class="bi bi-facebook"></i></a>
                <a href="https://twitter.com/yourprofile" class="mx-2 text-light"><i class="bi bi-twitter"></i></a>
                <a href="https://linkedin.com/in/yourprofile" class="mx-2 text-light"><i class="bi bi-linkedin"></i></a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.6/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../Public/Assets/Js/lineGraph.js"></script>
    <script src="../../Public/Assets/Js/calendar.js"></script>
</body>

</html>