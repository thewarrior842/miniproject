<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database configuration
    $host = "localhost";
    $db = "attc";
    $username = "root";
    $password = "";
    
    // Connect to database with error handling
    try {
        $conn = new mysqli($host, $username, $password, $db);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Set character set
        $conn->set_charset("utf8mb4");
        
        // Validate and sanitize inputs
        $name = htmlspecialchars(trim($_POST['name'] ?? ''));
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $date = htmlspecialchars(trim($_POST['date'] ?? ''));
        $bookingType = htmlspecialchars(trim($_POST['booking_type'] ?? ''));
        
        // Validate required fields
        if (empty($name) || empty($email) || empty($date) || empty($bookingType)) {
            throw new Exception("All required fields must be filled.");
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }
        
        // Process time slots based on booking type
        $timeSlots = [];
        
        if ($bookingType === 'single') {
            if (empty($_POST['time_slot'])) {
                throw new Exception("Please select a time slot.");
            }
            $timeSlots[] = htmlspecialchars(trim($_POST['time_slot']));
        } 
        elseif ($bookingType === 'multiple') {
            if (empty($_POST['time_slots'])) {
                throw new Exception("Please select at least one time slot.");
            }
            foreach ($_POST['time_slots'] as $slot) {
                $timeSlots[] = htmlspecialchars(trim($slot));
            }
        } 
        elseif ($bookingType === 'all') {
            $timeSlots = [
                '9:20 AM to 10:15 AM',
                '10:15 AM to 11:10 AM',
                '11:20 AM to 12:15 PM',
                '1:10 PM to 2:05 PM',
                '2:05 PM to 3:00 PM',
                '3:05 PM to 4:00 PM'
            ];
        }
        
        // Check for existing book to prevent conflicts
        $conflicts = [];
        foreach ($timeSlots as $slot) {
            $stmt = $conn->prepare("SELECT id FROM book WHERE date = ? AND time_slot = ?");
            $stmt->bind_param("ss", $date, $slot);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $conflicts[] = $slot;
            }
            $stmt->close();
        }
        
        if (!empty($conflicts)) {
            throw new Exception("The following time slots are already booked: " . implode(", ", $conflicts));
        }
        
        // Insert book into database
        $successCount = 0;
        foreach ($timeSlots as $slot) {
            $stmt = $conn->prepare("INSERT INTO book (name, email, date, time_slot, booking_type) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $email, $date, $slot, $bookingType);
            
            if ($stmt->execute()) {
                $successCount++;
            }
            $stmt->close();
        }
        
        // Display confirmation
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Booking Confirmation</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    text-align: center;
                    padding: 50px;
                    background-color: #f5f5f5;
                }
                .confirmation {
                    max-width: 600px;
                    margin: 0 auto;
                    background-color: white;
                    padding: 30px;
                    border-radius: 10px;
                    box-shadow: 0 0 15px rgba(0,0,0,0.1);
                }
                h2 {
                    color: #27ae60;
                }
                .details {
                    text-align: left;
                    margin: 20px 0;
                }
                .back-btn {
                    display: inline-block;
                    margin-top: 20px;
                    padding: 10px 20px;
                    background-color: #3498db;
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;
                }
            </style>
        </head>
        <body>
            <div class="confirmation">
                <h2>Booking Confirmed!</h2>
                <div class="details">
                    <p><strong>Name:</strong> ' . $name . '</p>
                    <p><strong>Email:</strong> ' . $email . '</p>
                    <p><strong>Date:</strong> ' . $date . '</p>
                    <p><strong>Booking Type:</strong> ' . ucfirst($bookingType) . '</p>
                    <p><strong>Time Slots:</strong></p>
                    <ul>';
        
        foreach ($timeSlots as $slot) {
            echo '<li>' . $slot . '</li>';
        }
        
        echo '</ul>
                </div>
                <p>You have successfully booked ' . $successCount . ' time slot(s).</p>
                <a href="main.html" class="back-btn">Make Another Booking</a>
                <a href="home.html" class="back-btn">Back to Home</a>
            </div>
        </body>
        </html>';
        
    } catch (Exception $e) {
        // Error handling
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Booking Error</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    text-align: center;
                    padding: 50px;
                    background-color: #f5f5f5;
                }
                .error {
                    max-width: 600px;
                    margin: 0 auto;
                    background-color: white;
                    padding: 30px;
                    border-radius: 10px;
                    box-shadow: 0 0 15px rgba(0,0,0,0.1);
                }
                h2 {
                    color: #e74c3c;
                }
                .back-btn {
                    display: inline-block;
                    margin-top: 20px;
                    padding: 10px 20px;
                    background-color: #3498db;
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;
                }
            </style>
        </head>
        <body>
            <div class="error">
                <h2>Booking Error</h2>
                <p>' . htmlspecialchars($e->getMessage()) . '</p>
                <a href="main.html" class="back-btn">Try Again</a>
            </div>
        </body>
        </html>';
    } finally {
        if (isset($conn)) {
            $conn->close();
        }
    }
} else {
    header("Location: main.html");
    exit();
}
?>
