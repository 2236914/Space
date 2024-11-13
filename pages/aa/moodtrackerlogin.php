<?php
session_start();
require 'configs/config.php'; // Include your database connection

// Check if the user is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: index.php"); // Redirect to login page if not logged in
    exit();
}

$student_id = $_SESSION['student_id']; // Assuming the student ID is stored in the session

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $emoticons = isset($_POST['emoticons']) ? $_POST['emoticons'] : '';
    $explanation = isset($_POST['explanation']) ? trim($_POST['explanation']) : '';

    // Prepare SQL statement to insert the mood tracking data
    $stmt = $pdo->prepare("INSERT INTO moodtracker (student_id, emoticons, explanation) VALUES (?, ?, ?)");
    $success = $stmt->execute([$student_id, $emoticons, $explanation]);

    // Check if the insertion was successful
    if ($success) {
        // Return success response
        echo json_encode(['success' => true]);
    } else {
        // Return error response
        echo json_encode(['success' => false]);
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mood Tracker</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #fff;
            margin: 0;
        }

        .tracker-container {
            text-align: center;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
        }

        h2 {
            margin: 10px 0;
            font-size: 24px;
        }

        .mood-button-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .mood-button {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin: 5px;
            cursor: pointer;
            font-size: 24px;
            background-color: #f0f0f0;
            border: 2px solid #ccc;
            transition: transform 0.3s, border-color 0.3s;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .mood-button:hover {
            transform: scale(1.1);
            border-color: #000;
        }

        .description {
            margin-bottom: 20px;
        }

        .description textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            resize: none;
        }

        button {
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            background-color: #000;
            color: #fff;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #444;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }

        .emoji-button {
            margin: 5px;
            cursor: pointer;
            font-size: 24px;
            background-color: transparent;
            border: none;
            outline: none;
        }
    </style>
</head>
<body>

    <div class="tracker-container">
        <h2>How are you feeling today?</h2>
        
        <div class="mood-button-container">
            <button class="mood-button" id="moodButton1" onclick="openModal(0)"></button>
            <button class="mood-button" id="moodButton2" onclick="openModal(1)"></button>
            <button class="mood-button" id="moodButton3" onclick="openModal(2)"></button>
            <button class="mood-button" id="moodButton4" onclick="openModal(3)"></button>
            <button class="mood-button" id="moodButton5" onclick="openModal(4)"></button>
        </div>

        <div class="description">
            <textarea id="explanation" name="explanation" rows="4" placeholder="Describe what's going on..."></textarea>
        </div>

        <button type="button" onclick="submitMood()">Send to Space</button>


        <!-- Success Message Div -->
        <div id="successMessage" style="display: none; color: green; margin-top: 20px;">
            Mood recorded successfully!
        </div>
    </div>

    <!-- Modal for selecting mood -->
    <div id="moodModal" class="modal">
        <div class="modal-content">
            <span onclick="closeModal()" style="cursor:pointer;">&times; Close</span>
            <h3>Select Mood</h3>
            <div>
                <button class="emoji-button" onclick="selectMood('😀')">😀</button>
                <button class="emoji-button" onclick="selectMood('😃')">😃</button>
                <button class="emoji-button" onclick="selectMood('😅')">😅</button>
                <button class="emoji-button" onclick="selectMood('😂')">😂</button>
                <button class="emoji-button" onclick="selectMood('🥲')">🥲</button>
                <button class="emoji-button" onclick="selectMood('😐')">😐</button>
                <button class="emoji-button" onclick="selectMood('😞')">😞</button>
                <button class="emoji-button" onclick="selectMood('😠')">😠</button>
                <button class="emoji-button" onclick="selectMood('😢')">😢</button>
                <button class="emoji-button" onclick="selectMood('😴')">😴</button>
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
    let moodButtons = document.querySelectorAll('.mood-button');
    let selectedButtonIndex = null;

    function openModal(index) {
        selectedButtonIndex = index;
        document.getElementById("moodModal").style.display = "block";
    }

    function closeModal() {
        document.getElementById("moodModal").style.display = "none";
    }

    function selectMood(mood) {
        moodButtons[selectedButtonIndex].textContent = mood; // Set the mood on the button
        closeModal(); // Close the modal after selection
    }

    window.openModal = openModal; // Make sure openModal function is globally available
    window.closeModal = closeModal; // Make sure closeModal function is globally available
    window.selectMood = selectMood; // Make sure selectMood function is globally available

    function submitMood() {
        console.log("Submit button clicked"); // Debugging step

        const explanation = document.getElementById('explanation').value;
        const selectedMoods = Array.from(moodButtons).map(button => button.textContent).filter(mood => mood !== "").join(", ");
        const studentId = <?php echo json_encode($student_id); ?>; // Get student ID from PHP session

        // Check if required fields are populated
        if (!selectedMoods || !explanation) {
            alert("Please select a mood and provide an explanation.");
            return;
        }

        // Prepare the data as JSON
        const data = {
            student_id: studentId,
            emoticons: selectedMoods,
            explanation: explanation
        };

        // Debugging step: log data to verify
        console.log("Data being sent to server:", data);

        // Use Fetch API to send JSON data to the server
        fetch('submit_mood.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            console.log("Received response from server:", response); // Debugging step
            return response.json();
        })
        .then(data => {
            if (data.success) {
                console.log("Mood recorded successfully"); // Debugging step
                const successMessage = document.getElementById('successMessage');
                successMessage.style.display = 'block';

                // Hide the success message after 3 seconds and redirect to students.php
                setTimeout(() => {
                    successMessage.style.display = 'none';
                    window.location.href = 'students.php'; // Redirect to students.php
                }, 3000);
            } else {
                console.error("Error recording mood"); // Debugging step
                alert('Error recording mood.');
            }
        })
        .catch((error) => {
            console.error('Error in fetch operation:', error);
        });
    }

    window.submitMood = submitMood; // Make sure submitMood function is globally available
});

</script>

</body>

</html>
