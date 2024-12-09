// Timer functionality
let timeLeft = 900; // 15 minutes in seconds
let timerId = null;
let isRunning = false;

function updateDisplay() {
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    document.getElementById('timer').textContent = 
        `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
}

function setTimer(minutes) {
    timeLeft = minutes * 60;
    updateDisplay();
    if (isRunning) {
        clearInterval(timerId);
        isRunning = false;
        document.getElementById('startBtn').innerHTML = '<i class="material-symbols-rounded">play_arrow</i> Start';
    }
}

function startTimer() {
    if (!isRunning) {
        isRunning = true;
        document.getElementById('startBtn').innerHTML = '<i class="material-symbols-rounded">pause</i> Pause';
        timerId = setInterval(() => {
            timeLeft--;
            updateDisplay();
            if (timeLeft === 0) {
                clearInterval(timerId);
                isRunning = false;
                document.getElementById('startBtn').innerHTML = '<i class="material-symbols-rounded">play_arrow</i> Start';
                new Audio('../../assets/sounds/bell.mp3').play();
            }
        }, 1000);
    } else {
        clearInterval(timerId);
        isRunning = false;
        document.getElementById('startBtn').innerHTML = '<i class="material-symbols-rounded">play_arrow</i> Start';
    }
}

function resetTimer() {
    clearInterval(timerId);
    isRunning = false;
    timeLeft = 900; // Reset to 15 minutes
    updateDisplay();
    document.getElementById('startBtn').innerHTML = '<i class="material-symbols-rounded">play_arrow</i> Start';
}

// Sound management
const sounds = {
    rain: new Audio('../../assets/sounds/rain.mp3'),
    forest: new Audio('../../assets/sounds/forest.mp3'),
    waves: new Audio('../../assets/sounds/waves.mp3'),
    birds: new Audio('../../assets/sounds/birds.mp3')
};

function toggleSound(soundName) {
    const sound = sounds[soundName];
    const button = event.currentTarget;
    
    if (sound.paused) {
        // Stop all other sounds
        Object.values(sounds).forEach(s => s.pause());
        document.querySelectorAll('.sound-button').forEach(btn => btn.classList.remove('active'));
        
        sound.loop = true;
        sound.volume = document.getElementById('volumeControl').value / 100;
        sound.play();
        button.classList.add('active');
    } else {
        sound.pause();
        button.classList.remove('active');
    }
}

// Volume control
document.addEventListener('DOMContentLoaded', () => {
    const volumeControl = document.getElementById('volumeControl');
    if (volumeControl) {
        volumeControl.addEventListener('input', (e) => {
            const volume = e.target.value / 100;
            Object.values(sounds).forEach(sound => sound.volume = volume);
        });
    }
});

// Breathing exercise functionality
let isBreathing = false;
let currentCycle = 0;
const totalCycles = 3;
let circle;
let breathingText;
let startBreathingBtn;

document.addEventListener('DOMContentLoaded', () => {
    circle = document.querySelector('.breathing-circle');
    breathingText = document.querySelector('.breathing-text');
    startBreathingBtn = document.getElementById('startBreathingBtn');
});

function startBreathing() {
    if (!isBreathing) {
        isBreathing = true;
        startBreathingBtn.innerHTML = '<i class="material-symbols-rounded">pause</i> Pause';
        breathingCycle();
    } else {
        isBreathing = false;
        startBreathingBtn.innerHTML = '<i class="material-symbols-rounded">play_arrow</i> Start';
    }
}

function resetBreathing() {
    isBreathing = false;
    currentCycle = 0;
    startBreathingBtn.innerHTML = '<i class="material-symbols-rounded">play_arrow</i> Start';
    circle.style.animation = 'none';
    breathingText.textContent = 'Breathe In';
    document.getElementById('cycleCount').textContent = `Cycles: ${currentCycle}/${totalCycles}`;
}

async function breathingCycle() {
    if (!isBreathing) return;

    // Breathe In - 4 seconds
    breathingText.textContent = 'Breathe In';
    circle.style.animation = 'breatheIn 4s forwards';
    await new Promise(resolve => setTimeout(resolve, 4000));
    if (!isBreathing) return;

    // Hold - 7 seconds
    breathingText.textContent = 'Hold';
    circle.style.animation = 'breatheHold 7s forwards';
    await new Promise(resolve => setTimeout(resolve, 7000));
    if (!isBreathing) return;

    // Breathe Out - 8 seconds
    breathingText.textContent = 'Breathe Out';
    circle.style.animation = 'breatheOut 8s forwards';
    await new Promise(resolve => setTimeout(resolve, 8000));
    if (!isBreathing) return;

    currentCycle++;
    document.getElementById('cycleCount').textContent = `Cycles: ${currentCycle}/${totalCycles}`;

    if (currentCycle < totalCycles && isBreathing) {
        breathingCycle();
    } else {
        resetBreathing();
    }
}

// Tab switching handler
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('button[data-bs-toggle="pill"]').forEach(button => {
        button.addEventListener('click', function() {
            if (isBreathing) {
                resetBreathing();
            }
        });
    });
}); 