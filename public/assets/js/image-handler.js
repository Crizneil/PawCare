/**
 * PawCare Image Handler
 * Handles File Input, Camera Capture, and Cropping via Cropper.js
 */

document.addEventListener('DOMContentLoaded', function () {
    let cropper = null;
    let currentInput = null;
    let currentHiddenInput = null;
    let currentPreview = null;
    let stream = null;

    const cropModal = new bootstrap.Modal(document.getElementById('imageCropModal'));
    const imageToCrop = document.getElementById('image-to-crop');
    const applyCropBtn = document.getElementById('apply-crop');
    const cameraContainer = document.getElementById('camera-container');
    const cropperContainer = document.getElementById('cropper-container');
    const webcam = document.getElementById('webcam');
    const takeSnapshotBtn = document.getElementById('take-snapshot');
    const snapshotCanvas = document.getElementById('snapshot-canvas');

    // --- 1. Global Listener for File Inputs ---
    document.addEventListener('change', function (e) {
        if (e.target.type === 'file' && (e.target.name === 'image' || e.target.name === 'profile_image')) {
            const file = e.target.files[0];
            if (!file) return;

            currentInput = e.target;
            
            // Find related hidden input and preview
            const form = currentInput.closest('form');
            currentHiddenInput = form.querySelector('input[type="hidden"][name$="_base64"]');
            currentPreview = form.querySelector('img[id$="Preview"]') || form.querySelector('img.profile-preview');

            const reader = new FileReader();
            reader.onload = function (event) {
                openCropper(event.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    // --- 2. Camera Trigger Support ---
    // We expect a button with class .btn-camera-trigger
    document.addEventListener('click', function (e) {
        const trigger = e.target.closest('.btn-camera-trigger');
        if (trigger) {
            e.preventDefault();
            currentInput = null; // We are capturing, not selecting file
            
            const form = trigger.closest('form');
            currentHiddenInput = form.querySelector('input[type="hidden"][name$="_base64"]');
            currentPreview = form.querySelector('img[id$="Preview"]') || form.querySelector('img.profile-preview');

            startCamera();
        }
    });

    function startCamera() {
        cameraContainer.classList.remove('d-none');
        cropperContainer.classList.add('d-none');
        applyCropBtn.classList.add('d-none');
        
        navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" }, audio: false })
            .then(s => {
                stream = s;
                webcam.srcObject = stream;
                cropModal.show();
            })
            .catch(err => {
                console.error("Camera error:", err);
                alert("Could not access camera. Please check permissions.");
            });
    }

    takeSnapshotBtn.addEventListener('click', function() {
        const context = snapshotCanvas.getContext('2d');
        snapshotCanvas.width = webcam.videoWidth;
        snapshotCanvas.height = webcam.videoHeight;
        context.drawImage(webcam, 0, 0, webcam.videoWidth, webcam.videoHeight);
        
        const dataUrl = snapshotCanvas.toDataURL('image/png');
        stopCamera();
        openCropper(dataUrl);
    });

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        cameraContainer.classList.add('d-none');
    }

    function openCropper(imageSrc) {
        imageToCrop.src = imageSrc;
        cropperContainer.classList.remove('d-none');
        applyCropBtn.classList.remove('d-none');
        
        cropModal.show();

        if (cropper) cropper.destroy();

        // Wait for modal to show before initializing cropper
        setTimeout(() => {
            cropper = new Cropper(imageToCrop, {
                aspectRatio: 1,
                viewMode: 1,
                autoCropArea: 1,
            });
        }, 300);
    }

    applyCropBtn.addEventListener('click', function () {
        if (!cropper) return;

        const canvas = cropper.getCroppedCanvas({
            width: 400,
            height: 400,
        });

        const base64Image = canvas.toDataURL('image/png');
        console.log("Cropped image generated. Length:", base64Image.length);

        // Update hidden input
        if (currentHiddenInput) {
            currentHiddenInput.value = base64Image;
            console.log("Hidden input updated:", currentHiddenInput.name);
        } else {
            console.error("Hidden input NOT found! Make sure your modal has a hidden input ending with _base64");
        }

        // Update preview if exists
        if (currentPreview) {
            currentPreview.src = base64Image;
        }

        cropModal.hide();
        if (cropper) cropper.destroy();
    });

    // Cleanup camera if modal is closed manually
    document.getElementById('imageCropModal').addEventListener('hidden.bs.modal', function () {
        stopCamera();
        if (cropper) cropper.destroy();
    });
});
