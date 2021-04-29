function openNav() {
    document.querySelector('#mySidenav').style.width = "325px"; 
    document.querySelector('.all-over-bkg').classList.add('is-visible');
}
  
function closeNav() {
document.querySelector('#mySidenav').style.width = "0"; 
document.querySelector('.all-over-bkg').classList.remove('is-visible');
}

document.querySelector('.openbtn').addEventListener('click', openNav);
document.querySelector('.closebtn').addEventListener('click', closeNav);