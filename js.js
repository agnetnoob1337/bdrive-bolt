console.log("JS loaded");
const fileCards = document.querySelectorAll('.fileCards'); 
resultCount = document.getElementById('resultCount')
resultCount.innerHTML = "Result:" +fileCards.length;

//Search
document.getElementById('searchBar').addEventListener('keyup', filterFiles);
function filterFiles() {
    const searchInput = document.getElementById('searchBar').value.toLowerCase();
    let searchFileCount = 0;
    fileCards.forEach(card => {
        const fileName = card.getAttribute('data-filename').toLowerCase();
        if (fileName.includes(searchInput)) {
            card.style.display = 'flex';
            searchFileCount++;
        } else {
            card.style.display = 'none';
        }
    });
    resultCount.innerHTML = "Result:" +searchFileCount;
}


//Sort
document.getElementById('sortBox').addEventListener('change', sortFiles);
function sortFiles() {
    const sortOption = document.getElementById('sortBox').value;
    const fileArray = Array.from(fileCards);

    fileArray.sort((a, b) => {
        if (sortOption === 'A-Z') {// Sort alphabetically (ascending)
            const nameA = a.getAttribute('data-filename').toLowerCase();
            const nameB = b.getAttribute('data-filename').toLowerCase();
            return nameA > nameB ? 1 : -1;
        } else if (sortOption === 'Z-A') {// Sort alphabetically (descending)
            const nameA = a.getAttribute('data-filename').toLowerCase();
            const nameB = b.getAttribute('data-filename').toLowerCase();
            return nameA < nameB ? 1 : -1;
        } else if (sortOption === 'Newest') {// Sort by date (newest first) (default)
            const dateA = new Date(a.getAttribute('data-date'));
            const dateB = new Date(b.getAttribute('data-date'));
            return dateB - dateA;
        } else if (sortOption === 'Oldest') {// Sort by date (oldest first)
            const dateA = new Date(a.getAttribute('data-date'));
            const dateB = new Date(b.getAttribute('data-date'));
            return dateA - dateB;
        } else if (sortOption === 'Type') {// Sort by filetype (group similar types together, then A-Z)
            const fileTypePriority = {
                image: 1,
                video: 2,
                audio: 3,
                document: 4,
                other: 5
            };
        
            const typeA = a.getAttribute('data-type') || 'other';
            const typeB = b.getAttribute('data-type') || 'other';
        
            const priorityA = fileTypePriority[typeA] || 999;
            const priorityB = fileTypePriority[typeB] || 999;
        
            if (priorityA !== priorityB) {
                return priorityA - priorityB; // Group similar types
            }
        
            // Within same group, sort A-Z
            const nameA = a.getAttribute('data-filename').toLowerCase();
            const nameB = b.getAttribute('data-filename').toLowerCase();
            return nameA.localeCompare(nameB);
        }        
    });

    // re-render filecards in sorted order
    const container = document.querySelector('#container');
    while (container.hasChildNodes()) {
        container.removeChild(container.firstChild);
    }
    fileArray.forEach(card => container.appendChild(card));
}



//file info box toggle
const toggleButtons = document.querySelectorAll('.toggleInfoBtn');
toggleButtons.forEach(button => {
    button.addEventListener('click', () => {
        const parentCard = button.closest('.fileCards');
        const infoBox = parentCard.querySelector('.fileInfo');

        if (infoBox.style.display === 'none') {
            infoBox.style.display = 'block';
        } else {
            infoBox.style.display = 'none';
        }
    });
});


//Rename
fileCards.forEach(card => {
    const editBtn = card.querySelector('.editBtn');
    const filenameDisplay = card.querySelector('.filenameDisplay');
    const filenameInput = card.querySelector('.filenameInput');
    const renameForm = card.querySelector('.renameForm');
    const newNameInput = card.querySelector('.newNameInput');
    const fileExtInput = card.querySelector('.fileExt');

    // Toggle input/display filename
    if (editBtn) {
        editBtn.addEventListener('click', () => {
            if (filenameDisplay.style.display != 'none'){
                filenameDisplay.style.display = 'none';
                filenameInput.style.display = 'inline';
            } else {
                filenameDisplay.style.display = 'inline';
                filenameInput.style.display = 'none'
            }
                renameForm.style.display = 'inline';
                filenameInput.focus();
        });
    }

    if (renameForm) {
        renameForm.addEventListener('submit', e => {
            e.preventDefault();
            const baseName = filenameInput.value.trim();
            const extension = fileExtInput.value.trim();

            if (!baseName) return;

            newNameInput.value = baseName + '.' + extension;
            renameForm.submit();
        });
    }
});


//Recursive search
document.getElementById('recursiveSearch').addEventListener('change', function() {
    document.getElementById('recursiveForm').submit();
});


// Lazy load videos (Load video only when onscreen)
const lazyVideos = document.querySelectorAll("video.lazy-video");
const videoObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const video = entry.target;

            // If already loaded, skip. else load it
            if (video.getAttribute("data-loaded") === "true") return;
            const src = video.getAttribute("data-src");
            const type = video.getAttribute("data-type");
            const source = document.createElement("source");
            source.src = src;
            source.type = type;
            video.appendChild(source);

            // Load only metadata to save performance
            video.preload = "metadata";
            video.setAttribute("data-loaded", "true");
            video.load();

            // When metadata is loaded remove poster and get video thumbnail
            video.addEventListener("loadedmetadata", () => {
                video.removeAttribute("poster");
                video.pause();
                video.currentTime = 0.01; // Set time to 0.01 to get thumbnail instead of black frame
            });
            observer.unobserve(video);
        }
    });
}, {
    rootMargin: "300px 0px",
    threshold: 0.1
});
lazyVideos.forEach(video => videoObserver.observe(video));

// Toggle folder controls
const toggleBtn = document.getElementById("toggleFolderControls");
if (toggleBtn) {
    toggleBtn.addEventListener("click", function () {
        document.querySelectorAll(".toggleVisible").forEach(fc => {
            fc.style.display = (fc.style.display === "none" || fc.style.display === "") 
                ? "block" 
                : "none";
        });
    });
}

// Fullscreen images on click
document.querySelectorAll('.clickableImage').forEach(img => {
    img.addEventListener('click', () => {
      if (document.fullscreenElement) {
        document.exitFullscreen();
      } else {
        img.requestFullscreen?.() || img.webkitRequestFullscreen?.() || img.msRequestFullscreen?.();
      }
    });
});

// Initial sort
sortFiles();