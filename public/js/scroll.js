// Sélectionne tous les éléments avec la classe .animate-block
const animateBlocks = document.querySelectorAll('.animate-block');

// Intersection Observer options
const options = {
    threshold: 0.1, // 20% de visibilité pour déclencher l'animation
    rootMargin: '0px 0px -50px 0px'
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

setTimeout(() => {
    animateBlocks.forEach(block => {
        block.classList.add('active');
    });
}, 1000);
