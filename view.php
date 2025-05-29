<?php
// Connect to the database
$host = "localhost";
$db = "attc";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $db);

// Check connection
if ($conn->connect_error) {
    die("<h3>Connection failed:</h3> " . htmlspecialchars($conn->connect_error));
}

// Query all bookings
$sql = "SELECT name, email, date, time  FROM bookings ";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Booking Listings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
        }

        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px;
            border: 1px solid #999;
        }

        th {
            background-color: #f0f0f0;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        h2 {
            margin-top: 40px;
        }

        a {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            background-color: teal;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <h2>All Bookings</h2>

    <?php if ($result && $result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Booking Date</th>
                <th>Booking Time</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['date']) ?></td>
                    <td><?= htmlspecialchars($row['time']) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No bookings found.</p>
    <?php endif; ?>

    <a href="home.html">Back to Home</a>
</body>

</html>

<?php
$conn->close();
?>