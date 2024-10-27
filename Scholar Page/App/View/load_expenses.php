<?php
$title = 'Scholar Load Expenses | SEDP HRMS';
$page = 'Scholar Load Expenses';
include('../../Core/Includes/header.php');
include("../../../Database/db.php");

$targetDir = "../../Uploads/";
$selectedMonth = isset($_GET['month']) ? strtolower($_GET['month']) : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reportTitle = $_POST['reportTitle'];
    $submissionDate = $_POST['submissionDate'];
    $reportContent = $_POST['reportContent'];
    $fileUpload = $_FILES['fileUpload'];

    if ($submissionDate) {
        $reportMonth = strtolower(date('F', strtotime($submissionDate)));
    } else {
        $reportMonth = '';
    }

    // Handle file upload
    $fileName = '';
    if (isset($fileUpload) && $fileUpload['error'] == 0) {
        // Generate a unique name for the file
        $fileName = uniqid() . '-' . basename($fileUpload['name']);
        $targetFilePath = $targetDir . $fileName;

        // Move the file to the target directory
        if (!move_uploaded_file($fileUpload['tmp_name'], $targetFilePath)) {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'File Upload Failed',
                    text: 'Sorry, there was an error uploading your file.',
                    confirmButtonColor: '#003c3c'
                });
            </script>";
            exit; // Stop execution if file upload fails
        }
    }

    // Insert data into the database
    try {
        $stmt = $pdo->prepare("INSERT INTO scholar_load_expenses (report_title, submission_date, report_content, file, report_month) 
                               VALUES (:report_title, :submission_date, :report_content, :file, :report_month)");

        // Bind parameters
        $stmt->bindParam(':report_title', $reportTitle);
        $stmt->bindParam(':submission_date', $submissionDate);
        $stmt->bindParam(':report_content', $reportContent);
        $stmt->bindParam(':file', $fileName); 
        $stmt->bindParam(':report_month', $reportMonth);

        if ($stmt->execute()) {
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Load Expenses submitted successfully!',
                    confirmButtonColor: '#003c3c'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'load_expenses.php';
                    }
                });
            </script>";
        } else {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Submission Failed',
                    text: 'Failed to submit the report.',
                    confirmButtonColor: '#003c3c'
                });
            </script>";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}


$submittedReports = [];
$stmt = $pdo->query("SELECT report_month FROM scholar_load_expenses");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $submittedReports[] = strtolower($row['report_month']);
}
?>

<style>
    html, body {
        overflow-x: hidden;
        background: linear-gradient(135deg, #e0f7fa, #ffffff);
    }

    #reportTitle::placeholder, #reportContent::placeholder {
        color: #000;
    }

    .month-card {
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .month-card .card-text {
        color: #6c757d;
    }

    .month-card .btn {
        background-color: #003c3c;
        color: #fff;
    }

    .month-card.selected {
        background-color: #003c3c;
        color: #fff;
    }

    .month-card.selected .card-title,
    .month-card.selected .card-text {
        color: #fff;
    }

</style>

<div class="container-fluid mx-2 bg-transparent" style="margin-top: 5.5rem;">
    <div class="row">
        <div class="col-md-4" style="max-height: 600px; overflow-y: auto; -ms-overflow-style: none; scrollbar-width: none; border-radius: 12px;">
            <div class="row">
                <?php 
                $months = [
                    'January', 'February', 'March', 'April', 'May', 
                    'June', 'July', 'August', 'September', 'October', 
                    'November', 'December'
                ];

                foreach ($months as $month) {
                    $lowercaseMonth = strtolower($month);
                    $isSelected = ($lowercaseMonth === $selectedMonth) ? 'selected' : '';
                    
                    $status = in_array($lowercaseMonth, $submittedReports) ? 'Submitted' : 'Pending';

                    echo '
                    <div class="col-12 mb-4">
                        <div id="month-' . $lowercaseMonth . '" class="card border-0 h-100 month-card ' . $isSelected . '" style="border-radius: 12px;">
                            <div class="card-body d-flex shadow-sm py-1 px-1" style="border-radius: 12px; border: 1px solid #003c3c;">
                                <div class="col-5 d-flex justify-content-center align-items-center" style="padding-right: 10px; width: 40%; ">
                                    <img src="../../Public/Assets/Images/narrative_report_month.png" alt="' . $month . '" style="width: 100%; height: 100%;border-radius: 12px 5px 5px 12px;">
                                </div>
                                <div class="col-7 pb-2" style="width: 60%;">
                                    <h5 class="card-title text-uppercase" style="font-weight: 600; letter-spacing: 0.5px; font-size: 1.7rem;">' . $month . '</h5>
                                    <p class="card-text fs-normal" style="font-size: 0.80rem; color: gray;">Click below to view or submit files.</p>
                                    <span class="status float-center " style="margin-bottom: 5px;font-size: 0.9rem; font-weight: 600; padding: 10px 34%;color: ' . ($status === 'Submitted' ? 'green' : 'red') . '; border-radius: 5px; background-color: ' . ($status === 'Submitted' ? '#d4edda' : '#f8d7da') . ';">' . $status . '</span>
                                </div>
                            </div>
                        </div>
                    </div>';
                }
                ?>
            </div>
        </div>

        <div class="col-md-8">
            <div class="container shadow-lg rounded-4 pb-5" style="padding: 1rem 4rem 0; background-color: #003c3c; height: 95%;">
                <h3 class="fw-bold fs-1 text-center mb-2 text-white">Load Expenses</h3>
                
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="mb-2">
                        <label for="reportTitle" class="form-label text-white">Report Title</label>
                        <input type="text" class="form-control shadow-sm border-0 bg-white text-dark" id="reportTitle" name="reportTitle" placeholder="Enter report title" required>
                        <div class="form-text text-white">Provide a concise and clear title for your report.</div>
                    </div>

                    <div class="mb-2">
                        <label for="submissionDate" class="form-label text-white">Submission Date</label>
                        <input type="date" class="form-control shadow-sm border-0 text-dark bg-white"  id="submissionDate" name="submissionDate" required>
                    </div>

                    <div class="mb-2">
                        <label for="reportContent" class="form-label text-white">Report Content</label>
                        <textarea class="form-control shadow-sm border-0 text-dark" style="height: 50px;" id="reportContent" name="reportContent" rows="8" placeholder="Enter detailed narrative report" required></textarea>
                        <div class="form-text text-white">Provide a comprehensive description of your report.</div>
                    </div>

                    <div class="mb-2">
                        <label for="fileUpload" class="form-label text-white">File Upload</label>
                        <input class="form-control" type="file" id="fileUpload" name="fileUpload" accept=".pdf,.doc,.docx,.ppt,.pptx" required>
                        <div class="form-text text-white">Upload any relevant files (PDF, DOC, PPT).</div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn bg-white fw-bold fs-6 px-5 py-2 btn-sm" style="color: #003c3c;">Submit Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const monthCards = document.querySelectorAll('.month-card');
    const submissionDateInput = document.getElementById('submissionDate');

    const monthToNumber = {
        'january': 1,
        'february': 2,
        'march': 3,
        'april': 4,
        'may': 5,
        'june': 6,
        'july': 7,
        'august': 8,
        'september': 9,
        'october': 10,
        'november': 11,
        'december': 12
    };

    monthCards.forEach(function(card) {
        card.addEventListener('click', function() {
            monthCards.forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');

            const monthTitle = this.querySelector('.card-title').textContent.trim().toLowerCase();
            const monthNumber = monthToNumber[monthTitle];

            if (monthNumber) {
                const currentYear = new Date().getFullYear();
                submissionDateInput.value = `${currentYear}-${monthNumber.toString().padStart(2, '0')}-01`;
            }
        });
    });
});
</script>


<?php include('../../Core/Includes/footer.php'); ?>
