<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Database credentials
    $host = "localhost";
    $db = "attc";
    $username = "root";
    $password = "";

    // Connect to database
    $conn = new mysqli($host, $username, $password, $db);

    // Check connection
    if ($conn->connect_error) {
        die("<h3>Connection failed:</h3> " . htmlspecialchars($conn->connect_error));
    }

    // Validate inputs
    $name = htmlspecialchars(trim($_POST["name"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $date = htmlspecialchars(trim($_POST["date"]));
    $time = htmlspecialchars(trim($_POST["time"]));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("<p>Invalid email format. Please go back and try again.</p>");
    }

    // Check for existing booking first
    $check_stmt = $conn->prepare("SELECT id FROM bookings WHERE date = ? AND time = ?");
    $check_stmt->bind_param("ss", $date, $time);
    $check_stmt->execute();
    $check_stmt->store_result();
    
    if ($check_stmt->num_rows > 0) {
        die("<h2>Double Booking Error</h2>
            <p>The selected time slot on date " . htmlspecialchars($date ) . " and time ". htmlspecialchars($time) ." is already booked.</p>
            <p>Please choose a different time</p>
            <p><a href='home.html'>Click here for book another day</a></p>");
    }
    $check_stmt->close();

    // Prepare statement
    $stmt = $conn->prepare("INSERT INTO bookings (name, email, date, time) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $date, $time);

    if ($stmt->execute()) {
        echo "<h2>Booking Confirmed</h2>";
        echo "<p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>";
        echo "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
        echo "<p><strong>Date:</strong> " . htmlspecialchars($date) . "</p>";
        echo "<p><strong>Time:</strong> " . htmlspecialchars($time) . "</p>";
        echo "<p><a href='home.html'>Return to Home</a></p>";
    } else {
        echo "<p>Error: " . htmlspecialchars($stmt->error) . "</p>";
    }

    // Close connections
    $stmt->close();
    $conn->close();
} else {
    // If not a POST request
    header("Location: home.html");
    exit();
}
?>
