<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sliding Navigation Bar</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
        }

        .navbar {
            position: relative;
            display: flex;
            background: #fff;
            padding: 10px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);justify-content: flex-start;
        }

        .nav-item {
            position: relative;
            padding: 10px 20px;
            cursor: pointer;
            color: #555;
            font-size: 16px;
            transition: color 0.3s ease-in-out;
        }

        .nav-item.active {
            color: #007bff;
        }

        /* Underline effect */
        .underline {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            width: 0;
            background: #007bff;
            transition: all 0.3s ease-in-out;
            border-radius: 5px;
        }

    </style>
</head>
<body>

    <div class="navbar">
        <div class="nav-item active" onclick="moveIndicator(this)">Home</div>
        <div class="nav-item" onclick="moveIndicator(this)">About</div>
        <div class="nav-item" onclick="moveIndicator(this)">Services</div>
        <div class="nav-item" onclick="moveIndicator(this)">Contact</div>
        <div class="underline"></div>
    </div>

    <script>
        function moveIndicator(element) {
            let navbar = document.querySelector(".navbar");
            let underline = document.querySelector(".underline");
            let activeItem = document.querySelector(".nav-item.active");

            if (activeItem) {
                activeItem.classList.remove("active");
            }

            element.classList.add("active");

            // Move the underline to the clicked tab
            underline.style.width = `${element.offsetWidth}px`;
            underline.style.transform = `translateX(${element.offsetLeft - navbar.offsetLeft}px)`;
        }

        // Initialize underline position on page load
        window.onload = () => {
            let activeElement = document.querySelector(".nav-item.active");
            let underline = document.querySelector(".underline");
            let navbar = document.querySelector(".navbar");

            if (activeElement) {
                underline.style.width = `${activeElement.offsetWidth}px`;
                underline.style.transform = `translateX(${activeElement.offsetLeft - navbar.offsetLeft}px)`;
            }
        };
    </script>

</body>
</html>

<!---Berlin---->