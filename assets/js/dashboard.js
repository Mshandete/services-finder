const menuToggle=document.getElementById("menuToggle");
const sidebar=document.querySelector(".sidebar");

if(menuToggle){

menuToggle.addEventListener("click",()=>{

sidebar.classList.toggle("show");

});

}

const observer=new IntersectionObserver((entries)=>{

entries.forEach((entry)=>{

if(entry.isIntersecting){

entry.target.classList.add("show");

}

});

},{threshold:.15});

document.querySelectorAll(".reveal").forEach(el=>observer.observe(el));

document.querySelectorAll(".reveal-item").forEach((el,index)=>{

el.style.transitionDelay=(index*0.15)+"s";

observer.observe(el);

});

document.querySelectorAll(".reveal,.reveal-item").forEach(el=>revealObserver.observe(el));
document.querySelectorAll(".nearby-grid .reveal-item").forEach((el,index)=>{
    el.style.transitionDelay=`${index*.12}s`});