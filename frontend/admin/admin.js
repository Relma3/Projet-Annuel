const API = "http://localhost/PA_2EME_ANNEE/api/index.php"

function token(){
return localStorage.getItem("token")
}

async function chargerSeniors(){

const res = await fetch(API+"/admin/seniors",{
headers:{
"Authorization":"Bearer "+token()
}
})

const data = await res.json()

const ul = document.getElementById("liste-seniors")
ul.innerHTML=""

data.forEach(s=>{
const li=document.createElement("li")
li.textContent=s.email
ul.appendChild(li)
})

}


async function chargerPrestataires(){

const res = await fetch(API+"/admin/prestataires",{
headers:{
"Authorization":"Bearer "+token()
}
})

const data = await res.json()

const ul=document.getElementById("liste-prestataires")
ul.innerHTML=""

data.forEach(p=>{
const li=document.createElement("li")
li.textContent=p.email
ul.appendChild(li)
})

}


async function chargerCategories(){

const res=await fetch(API+"/admin/categories",{
headers:{
"Authorization":"Bearer "+token()
}
})

const data=await res.json()

const ul=document.getElementById("liste-categories")
ul.innerHTML=""

data.forEach(c=>{

const li=document.createElement("li")
li.innerHTML=c.nom+" <button onclick='supprimerCategorie("+c.id+")'>X</button>"

ul.appendChild(li)

})

}


async function ajouterCategorie(){

const nom=document.getElementById("cat-nom").value
const description=document.getElementById("cat-desc").value

await fetch(API+"/admin/categories",{

method:"POST",

headers:{
"Content-Type":"application/json",
"Authorization":"Bearer "+token()
},

body:JSON.stringify({
nom:nom,
description:description
})

})

chargerCategories()

}


async function supprimerCategorie(id){

await fetch(API+"/admin/categories/"+id,{
method:"DELETE",
headers:{
"Authorization":"Bearer "+token()
}
})

chargerCategories()

}



async function chargerEvenements(){

const res=await fetch(API+"/admin/evenements",{
headers:{
"Authorization":"Bearer "+token()
}
})

const data=await res.json()

const ul=document.getElementById("liste-evenements")
ul.innerHTML=""

data.forEach(e=>{

const li=document.createElement("li")

li.innerHTML=e.titre+" "+e.date_debut+" <button onclick='supprimerEvenement("+e.id+")'>X</button>"

ul.appendChild(li)

})

}



async function ajouterEvenement(){

const titre=document.getElementById("ev-titre").value
const date=document.getElementById("ev-date").value
const lieu=document.getElementById("ev-lieu").value
const places=document.getElementById("ev-places").value


await fetch(API+"/admin/evenements",{

method:"POST",

headers:{
"Content-Type":"application/json",
"Authorization":"Bearer "+token()
},

body:JSON.stringify({

titre:titre,
date_debut:date,
lieu:lieu,
nombre_places:places

})

})

chargerEvenements()

}



async function supprimerEvenement(id){

await fetch(API+"/admin/evenements/"+id,{
method:"DELETE",
headers:{
"Authorization":"Bearer "+token()
}
})

chargerEvenements()

}