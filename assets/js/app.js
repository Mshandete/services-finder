let latitude = null;
let longitude = null;

function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(showPosition);
    } else {
        document.getElementById("gpsStatus").innerText = "GPS not supported";
    }
}

function showPosition(position) {
    latitude = position.coords.latitude;
    longitude = position.coords.longitude;

    document.getElementById("gpsStatus").innerText =
        "Location captured successfully ✔";
}

document.getElementById("serviceForm").addEventListener("submit", function(e) {
    e.preventDefault();

    const data = {
        service_name: document.getElementById("service_name").value,
        category: document.getElementById("category").value,
        phone: document.getElementById("phone").value,
        location_name: document.getElementById("location_name").value,
        latitude: latitude,
        longitude: longitude
    };

    fetch("../backend/add_service.php", {
        method: "POST",
        body: JSON.stringify(data),
        headers: {
            "Content-Type": "application/json"
        }
    })
    .then(res => res.json())
    .then(res => {
        alert(res.message);
    });
});