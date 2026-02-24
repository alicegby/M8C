// Sélectionne tous les éléments avec la classe .animate-block
const animateBlocks = document.querySelectorAll('.animate-block');

// Intersection Observer options
const options = {
    threshold: 0.2 // 20% de visibilité pour déclencher l'animation
};

// Callback de l'observer
const observer = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
        if(entry.isIntersecting){
            entry.target.classList.add('active'); // ajoute la classe active quand visible
            observer.unobserve(entry.target); // stop l'observation si on veut que ça n'arrive qu'une fois
        }
    });
}, options);

// On observe chaque bloc
animateBlocks.forEach(block => {
    observer.observe(block);
});