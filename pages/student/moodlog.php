<?php
session_start();
require_once '../../configs/config.php';
require_once '../../admin_operations/check_mood_logged.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Mood</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
  <link href="../../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../../assets/css/nucleo-svg.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twemoji/13.1.0/twemoji.min.css">
  <link id="pagestyle" href="../../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
</head>

<body>
  <main>
    <section class="py-6">
      <div class="container">
      <div class="col-md-6 mx-auto text-center">
        <h1 class="pt-5 pb-7">Are you <span id="typed"></span></h1>
          <div id="typed-strings" style="display: none;">
              <p>happy <img src="https://em-content.zobj.net/source/telegram/386/relieved-face_1f60c.webp" alt="happy" style="width: 50px; height: 50px;" />?</p>
              <p>sad <img src="https://em-content.zobj.net/source/telegram/386/crying-face_1f622.webp" alt="sad" style="width: 50px; height: 50px;" />?</p>
              <p>excited <img src="https://em-content.zobj.net/source/telegram/386/star-struck_1f929.webp" alt="excited" style="width: 50px; height: 50px;"  />?</p>
              <p>anxious <img src="https://em-content.zobj.net/source/telegram/386/anxious-face-with-sweat_1f630.webp" alt="anxious" style="width: 50px; height: 50px;"  />?</p>
              <p>calm <img src="https://em-content.zobj.net/source/telegram/386/relieved-face_1f60c.webp" alt="calm" style="width: 50px; height: 50px;"  />?</p>
              <p>frustrated <img src="https://em-content.zobj.net/source/telegram/386/angry-face_1f620.webp" alt="frustrated" style="width: 50px; height: 50px;"  />?</p>
              <p>tired <img src="https://em-content.zobj.net/source/telegram/386/dizzy-face_1f635.webp" alt="tired" style="width: 50px; height: 50px;"  />?</p>
          </div>
        <h4>How are you feeling today?</h4>
      </div>
        <div class="row">
          <div class="col-md-4 mx-auto">
            <div class="d-flex justify-content-center align-items-center py-auto gap-1 mx-auto" data-bs-toggle="modal" data-bs-target="#emojiModal">
              <button class="btn btn-light rounded-circle mood-button" style="width: 45px; height:45px; font-size: 18px;" id="moodButton1" onclick="openModal(0)" data-bs-toggle="tooltip" data-bs-placement="top" title="Choose emoji"></button></button>
              <button class="btn btn-light rounded-circle mood-button" style="width: 45px; height:45px; font-size: 18px;" id="moodButton2" onclick="openModal(1)" data-bs-toggle="tooltip" data-bs-placement="top" title="Choose emoji"></button>
              <button class="btn btn-light rounded-circle mood-button" style="width: 45px; height:45px; font-size: 18px;" id="moodButton3" onclick="openModal(2)" data-bs-toggle="tooltip" data-bs-placement="top" title="Choose emoji"></button>
              <button class="btn btn-light rounded-circle mood-button" style="width: 45px; height:45px; font-size: 18px;" id="moodButton4" onclick="openModal(3)" data-bs-toggle="tooltip" data-bs-placement="top" title="Choose emoji"></button>
              <button class="btn btn-light rounded-circle mood-button" style="width: 45px; height:45px; font-size: 18px;" id="moodButton5" onclick="openModal(4)" data-bs-toggle="tooltip" data-bs-placement="top" title="Choose emoji"></button>
            </div>
      <form id="moodForm">
            <input type="hidden" id="selected_emoji" name="selected_emoji" required>
            <input type="hidden" id="mood_name" name="mood_name" required>
            <div class="input-group input-group-outline mb-4"  style="max-width: 450px; margin: auto;">
              <textarea name="description" class="form-control" id="description" rows="4" 
                  placeholder="Describe what's going on (minimum 350 characters)" 
                  minlength="350" 
                  required
                  oninput="updateCharacterCount(this)"></textarea>
              <small id="charCount" class="form-text text-muted position-absolute" style="bottom: -20px; right: 0;">
                  0/350 characters
              </small>
            </div>
            <div class="row">
              <div class="col-md-12 d-flex justify-content-center">
                <button type="submit" class="btn btn-primary mt-1 btn-responsive w-100">Send to Space</button>
              </div>
            </div>
      </form>
        <div class="modal fade" id="modal-notification" tabindex="-1" role="dialog" aria-labelledby="modal-notification" aria-hidden="true">
          <div class="modal-dialog modal-danger modal-dialog-centered modal-" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h6 class="modal-title" id="modal-title-notification">Mood Logged Successfully!</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                    </button>
                  </div>
                <div class="modal-body">
                  <div class="py-3 text-center">
                    <img src="https://em-content.zobj.net/source/telegram/386/people-hugging_1fac2.webp" alt="People Hugging" style="width: 100px; height: 100px;">
                      <h4 class="text-primary mt-4">We’re Here with You</h4>
                      <p>Thank you for sharing how you're feeling today. We’re here to support you, whatever mood you're in.</p>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-primary btn-sm">Continue</button>
                </div>
            </div>
          </div>
        </div>
        </div>
          <div class="modal fade" id="emojiModal" tabindex="-1" aria-labelledby="emojiModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="emojiModalLabel">Choose an Emoji</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                <div class="container">
                  <div class="row justify-content-center align-items-center">
                      <div class="col-2">
                              <button class="rounded-circle emoji-button" style="width: 50px; height: 50px; font-size: 40px; background-color: transparent; border: none;" ms-code-emoji="https://em-content.zobj.net/source/apple/354/beaming-face-with-smiling-eyes_1f601.png" onclick="selectMood('😊')" data-bs-toggle="tooltip" data-bs-placement="top" title="Happy">😊</button>
                      </div>
                      <div class="col-2">
                              <button class="rounded-circle emoji-button" style="width: 50px; height: 50px; font-size: 40px; background-color: transparent; border: none;" ms-code-emoji="https://em-content.zobj.net/source/apple/391/frowning-face_2639-fe0f.png" onclick="selectMood('☹️')" data-bs-toggle="tooltip" data-bs-placement="top" title="Sad">☹️</button> 
                      </div>
                      <div class="col-2">
                        <button class="rounded-circle emoji-button" style="width: 50px; height: 50px; font-size: 40px; background-color: transparent; border: none;" ms-code-emoji="https://em-content.zobj.net/source/apple/354/angry-face_1f620.png" onclick="selectMood('😠')" data-bs-toggle="tooltip" data-bs-placement="top" title="Angry">😠</button>
                      </div>
                      <div class="col-2">
                              <button class="rounded-circle emoji-button" style="width: 50px; height: 50px; font-size: 40px; background-color: transparent; border: none;" ms-code-emoji="https://em-content.zobj.net/source/apple/354/relieved-face_1f60c.png" onclick="selectMood('😌')" data-bs-toggle="tooltip" data-bs-placement="top" title="Calm">😌</button> 
                      </div>
                      <div class="col-2">
                        <button class="rounded-circle emoji-button" style="width: 50px; height: 50px; font-size: 40px; background-color: transparent; border: none;" ms-code-emoji="https://em-content.zobj.net/source/apple/354/fearful-face_1f628.png" onclick="selectMood('😨')" data-bs-toggle="tooltip" data-bs-placement="top" title="Fearful">😨</button>
                      </div>
                  </div>
                  <div class="row justify-content-center align-items-center mt-2">
                      <div class="col-2">
                        <button class="rounded-circle emoji-button" style="width: 50px; height: 50px; font-size: 40px; background-color: transparent; border: none;" ms-code-emoji="https://em-content.zobj.net/source/apple/354/smiling-face-with-hearts_1f970.png" onclick="selectMood('😍')" data-bs-toggle="tooltip" data-bs-placement="top" title="Love">😍</button> 
                      </div>
                      <div class="col-2">
                         <button class="rounded-circle emoji-button" style="width: 50px; height: 50px; font-size: 40px; background-color: transparent; border: none;" ms-code-emoji="https://em-content.zobj.net/source/apple/354/pensive-face_1f614.png" onclick="selectMood('😔')" data-bs-toggle="tooltip" data-bs-placement="top" title="Disappointed">😔</button>
                      </div>
                      <div class="col-2">
                        <button class="rounded-circle emoji-button" style="width: 50px; height: 50px; font-size: 40px; background-color: transparent; border: none;" ms-code-emoji="https://em-content.zobj.net/source/apple/354/face-with-diagonal-mouth_1fae4.png" onclick="selectMood('😕')" data-bs-toggle="tooltip" data-bs-placement="top" title="Confused">😕</button> 
                      </div>
                      <div class="col-2">
                        <button class="rounded-circle emoji-button" style="width: 50px; height: 50px; font-size: 40px; background-color: transparent; border: none;" ms-code-emoji="https://em-content.zobj.net/source/apple/354/sleeping-face_1f634.png" onclick="selectMood('😴')" data-bs-toggle="tooltip" data-bs-placement="top" title="Tired / Sleepy">😴</button> 
                      </div>
                      <div class="col-2">
                        <button class="rounded-circle emoji-button" style="width: 50px; height: 50px; font-size: 40px; background-color: transparent; border: none;" ms-code-emoji="https://em-content.zobj.net/source/apple/354/thinking-face_1f914.png" onclick="selectMood('🤔')" data-bs-toggle="tooltip" data-bs-placement="top" title="Thoughtful/Reflective">🤔</button> 
                      </div>
                  </div>
                </div>
              </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
    <!-- Add this right before the closing </body> tag -->
  <div class="modal fade" id="moodModal" tabindex="-1" role="dialog" aria-labelledby="moodModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content text-center p-4">
        <div class="modal-body">
          <img src="https://em-content.zobj.net/source/telegram/452/people-hugging_1fac2.png" alt="Support" class="img-fluid mb-3" style="width: 100px; height: auto;">
          <h5 class="modal-title" id="moodModalLabel">Mood Logged Successfully!</h5>
          <p class="text-muted">Thank you for sharing how you're feeling today. We're here to support you, whatever mood you're in.</p>
        </div>
        <div class="modal-footer border-0 justify-content-center">
          <button type="button" class="btn bg-gradient-primary" onclick="redirectToDashboard()">Continue</button>
        </div>
      </div>
    </div>
  </div>
  <div class="position-fixed bottom-1 end-1 z-index-2">
    <!-- Toast for missing emojis -->
    <div class="toast fade hide p-2 bg-white" role="alert" aria-live="assertive" id="emojiToast" aria-atomic="true">
        <div class="toast-header border-0">
            <i class="material-symbols-rounded text-warning me-2">travel_explore</i>
            <span class="me-auto font-weight-bold">Emoji Selection Required</span>
            <i class="fas fa-times text-md ms-3 cursor-pointer" data-bs-dismiss="toast" aria-label="Close"></i>
        </div>
        <hr class="horizontal dark m-0">
        <div class="toast-body">
            Please select all 5 emojis to express your mood.
        </div>
    </div>

    <!-- Toast for missing description -->
    <div class="toast fade hide p-2 mt-2 bg-white" role="alert" aria-live="assertive" id="descriptionToast" aria-atomic="true">
        <div class="toast-header border-0">
            <i class="material-symbols-rounded text-warning me-2">campaign</i>
            <span class="me-auto font-weight-bold">Description Required</span>
            <i class="fas fa-times text-md ms-3 cursor-pointer" data-bs-dismiss="toast" aria-label="Close"></i>
        </div>
        <hr class="horizontal dark m-0">
        <div class="toast-body">
            Please enter at least 350 characters with letters and special characters.
        </div>
    </div>

    <!-- Toast for no data entered -->
    <div class="toast fade hide p-2 mt-2 bg-gradient-info" role="alert" aria-live="assertive" id="noDataToast" aria-atomic="true">
        <div class="toast-header bg-transparent border-0">
            <i class="material-symbols-rounded text-white me-2">notifications</i>
            <span class="me-auto text-white font-weight-bold">No Data Entered</span>
            <i class="fas fa-times text-md text-white ms-3 cursor-pointer" data-bs-dismiss="toast" aria-label="Close"></i>
        </div>
        <hr class="horizontal light m-0">
        <div class="toast-body text-white">
            Please select emojis and enter your description before submitting.
        </div>
    </div>
</div>


  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/typed.js@2.0.12"></script>
  <script>
    var tooltipTriggerList = Array.from(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })
  </script>
  <script type="text/javascript">
  if (document.getElementById('typed')) {
    var typed = new Typed("#typed", {
      stringsElement: '#typed-strings',
      typeSpeed: 90,
      backSpeed: 60,
      backDelay: 1000,
      startDelay: 500,
      loop: true
    });
  }
  </script>
  <script>
  document.querySelectorAll('[ms-code-emoji]').forEach(element => {
  var imageUrl = element.getAttribute('ms-code-emoji');
  var img = document.createElement('img');
  img.src = imageUrl;

  var textStyle = window.getComputedStyle(element);
  var adjustedHeight = parseFloat(textStyle.fontSize) * 1.0;

  img.style.height = adjustedHeight + 'px';
  img.style.width = 'auto';
  img.style.verticalAlign = 'text-top';

  element.innerHTML = '';
  element.appendChild(img);
});
  </script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    let moodButtons = document.querySelectorAll('.mood-button');
    let selectedButtonIndex = null;
    const moodForm = document.getElementById('moodForm');
    let selectedEmojis = new Array(5).fill(null); // Track selected emojis

    function openModal(index) {
        selectedButtonIndex = index;
    }

    function selectMood(mood) {
        if (selectedButtonIndex !== null) {
            moodButtons[selectedButtonIndex].textContent = mood;
            selectedEmojis[selectedButtonIndex] = mood;
            updateEmojiValidation();
            closeModal();
        }
    }

    function updateEmojiValidation() {
        const selectedCount = selectedEmojis.filter(emoji => emoji !== null).length;
        const remainingCount = 5 - selectedCount;
        
        if (selectedCount < 5) {
            const toast = new bootstrap.Toast(document.getElementById('emojiToast'));
            document.querySelector('#emojiToast .toast-body').textContent = 
                `Please select ${remainingCount} more emoji${remainingCount > 1 ? 's' : ''}`;
            toast.show();
        }
        
        // Update hidden input with all selected emojis
        document.getElementById('selected_emoji').value = selectedEmojis.filter(e => e).join(',');
        // Update mood names
        document.getElementById('mood_name').value = selectedEmojis
            .filter(e => e)
            .map(emoji => getMoodName(emoji))
            .join(',');
    }

    function getMoodName(emoji) {
      const moodMap = {
        '😊': 'Happy',
        '☹️': 'Sad',
        '😠': 'Angry',
        '😌': 'Calm',
        '😨': 'Fearful',
        '😍': 'Love',
        '😔': 'Disappointed',
        '😕': 'Confused',
        '😴': 'Tired',
        '🤔': 'Thoughtful'
      };
      return moodMap[emoji] || 'Unknown';
    }

    function updateCharacterCount(textarea) {
        const charCount = textarea.value.length;
        const minChars = 350;
        document.getElementById('charCount').textContent = 
            `${charCount}/${minChars} characters`;
        
        // Optional: Change color based on count
        if (charCount < minChars) {
            document.getElementById('charCount').style.color = '#dc3545';
        } else {
            document.getElementById('charCount').style.color = '#198754';
        }
    }

    function validateDescription(text) {
        // Check length
        if (text.length < 350) {
            return false;
        }
        
        // Check for mix of letters and special characters
        const hasLetters = /[a-zA-Z]/.test(text);
        const hasSpecialChars = /[!@#$%^&*(),.?":{}|<>]/.test(text);
        
        return hasLetters && hasSpecialChars;
    }

    function redirectToDashboard() {
        window.location.href = 'student.php';
    }

    moodForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const description = document.getElementById('description').value;
        const selectedCount = selectedEmojis.filter(emoji => emoji !== null).length;
        
        // Case 3: No data entered at all
        if (selectedCount === 0 && description.trim() === '') {
            const noDataToast = new bootstrap.Toast(document.getElementById('noDataToast'));
            noDataToast.show();
            return;
        }
        
        // Case 1: No emojis but has description
        if (selectedCount < 5 && description.trim() !== '') {
            const emojiToast = new bootstrap.Toast(document.getElementById('emojiToast'));
            document.querySelector('#emojiToast .toast-body').textContent = 
                `Please select ${5 - selectedCount} more emoji${5 - selectedCount > 1 ? 's' : ''} to express your mood`;
            emojiToast.show();
            return;
        }
        
        // Case 2: Has emojis but no/invalid description
        if (selectedCount > 0 && !validateDescription(description)) {
            const descriptionToast = new bootstrap.Toast(document.getElementById('descriptionToast'));
            descriptionToast.show();
            return;
        }

        // If all validations pass, proceed with form submission
        const formData = new FormData(this);
        formData.append('srcode', '<?php echo $_SESSION['user_id']; ?>');

        try {
            const response = await fetch('../../admin_operations/save_mood.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.status === 'success') {
                const moodModal = new bootstrap.Modal(document.getElementById('moodModal'));
                moodModal.show();
            } else {
                throw new Error(result.message || 'Failed to log mood');
            }
        } catch (error) {
            const dangerToast = new bootstrap.Toast(document.getElementById('dangerToast'));
            document.querySelector('#dangerToast .toast-body').textContent = 
                error.message || 'Something went wrong!';
            dangerToast.show();
        }
    });

    function closeModal() {
      const modalEl = document.getElementById('emojiModal');
      const modal = bootstrap.Modal.getInstance(modalEl);
      if (modal) {
        modal.hide();
      }
    }

    window.openModal = openModal;
    window.selectMood = selectMood;
    window.closeModal = closeModal;
    window.updateCharacterCount = updateCharacterCount;
    window.redirectToDashboard = redirectToDashboard;
  });
</script>

</body>
</html>
