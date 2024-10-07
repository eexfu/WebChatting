document.addEventListener("DOMContentLoaded", function() {
    const uploadBtn = document.getElementById('uploadBtn');
    const fileInput = document.getElementById('fileInput');
    const uploadResult = document.getElementById('upload-result');
    const userImage = document.querySelector('.user-image');

    const serverIp = JSON.parse(document.querySelector('meta[name="serverIp"]').getAttribute('data-serverIp'));
    const userId = JSON.parse(document.querySelector('meta[name="userId"]').getAttribute('data-userId'));

    uploadBtn.addEventListener('click', function () {
        fileInput.click();
    });

    fileInput.addEventListener('change', async function () {
        const formData = new FormData();
        const file = fileInput.files[0];

        if (file) {
            formData.append('imageFile', file);
            formData.append('userId', userId);

            try {
                const response = await fetch(`${serverIp}/settings/upload-icon`, {
                    method: 'POST',
                    body: formData,
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok ' + response.statusText);
                }

                const result = await response.text();
                uploadResult.innerHTML = '<p>' + result + '</p>';
                userImage.src = URL.createObjectURL(file);
            } catch (error) {
                uploadResult.innerHTML = '<p>Error: ' + error.message + '</p>';
            }
        }
    });
});
