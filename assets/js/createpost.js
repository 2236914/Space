// Handle image selection and preview
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImage() {
    document.getElementById('imageInput').value = '';
    document.getElementById('imagePreview').style.display = 'none';
}

const badWords = [
    // English General Profanity
    'fuck', 'shit', 'ass', 'bitch', 'damn', 'crap', 'piss', 'motherfucker',

    // English Sexual/Explicit Content
    'pussy', 'cunt', 'dildo', 'faggot', 'slut', 'whore',

    // English Insults
    'idiot', 'moron', 'dumb', 'stupid', 'retard',

    // Tagalog General Swear Words
    'putangina', 'tarantado', 'gago', 'tanga', 'bobo', 'siraulo', 'lintik',

    // Tagalog Sexual/Explicit Content
    'puke', 'betlog', 'titi','tite  ', 'kepyas', 'kantot', 'biyak', 'laplap',

    // Tagalog Insults
    'ulol', 'engot', 'tangan-tanga', 'gunggong',

    // Tagalog Other Derogatory Terms
    'leche', 'pakshet', 'bwisit'
];

function containsBadWords(text) {
    const lowerText = text.toLowerCase();
    return badWords.some(word => {
        const pattern = new RegExp(`\\b${word.replace(/[aieos]/g, char => {
            switch (char) {
                case 'a': return '(a|@|4)';
                case 'i': return '(i|1|!)';
                case 'e': return '(e|3)';
                case 'o': return '(o|0)';
                case 's': return '(s|5|\\$)';
                default: return char;
            }
        })}\\b`, 'i');
        return pattern.test(lowerText);
    });
}

function createPost() {
    // Debug logs
    console.log("Starting post creation...");
    console.log("Session data:", document.cookie);

    // Get the content and image
    const contentTextarea = document.querySelector('textarea[name="content"]');
    const imageInput = document.getElementById('imageInput');
    
    // Debug content
    console.log("Content:", contentTextarea?.value);
    
    // Validate content
    if (!contentTextarea || !contentTextarea.value.trim()) {
        Swal.fire({
            icon: 'warning',
            title: 'Empty Post',
            text: 'Please write something before posting.'
        });
        return;
    }

    // Check for bad words
    if (containsBadWords(contentTextarea.value)) {
        Swal.fire({
            icon: 'error',
            title: 'Inappropriate Content',
            text: 'Your message contains inappropriate language. Please revise and try again.'
        });
        return;
    }

    // Create FormData object
    const formData = new FormData();
    formData.append('content', contentTextarea.value.trim());

    // Add image if selected
    if (imageInput && imageInput.files.length > 0) {
        formData.append('image', imageInput.files[0]);
        console.log("Image added to form data");
    }

    // Show loading state
    Swal.fire({
        title: 'Creating post...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Debug request
    console.log("Sending request to create post...");

    // Send request with absolute path
    fetch('/Space/admin_operations/create_post.php', {
        method: 'POST',
        body: formData,
        credentials: 'include'
    })
    .then(response => {
        console.log("Response status:", response.status);
        return response.json().then(data => {
            if (!response.ok) {
                throw new Error(data.message || `HTTP error! status: ${response.status}`);
            }
            return data;
        });
    })
    .then(data => {
        console.log("Success response:", data);
        if (data.status === 'success') {
            // Clear form
            contentTextarea.value = '';
            if (imageInput) {
                imageInput.value = '';
                removeImage();
            }

            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Posted!',
                text: 'Your post has been created successfully',
                timer: 1500
            }).then(() => {
                window.location.reload();
            });
        } else {
            throw new Error(data.message || 'Failed to create post');
        }
    })
    .catch(error => {
        console.error('Post creation error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Post Failed',
            text: error.message || 'Something went wrong! Please try again.'
        });
    });
}