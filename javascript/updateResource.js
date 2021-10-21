const resourcesUrlNode = document.getElementById('resourcesUrl')
const saveUrlNode = document.getElementsByClassName('saveUrl')
const urlLength = saveUrlNode.length

for (let i = 0; i < urlLength; i++) {
  saveUrlNode[i].innerText = resourcesUrlNode.value
}

resourcesUrlNode.addEventListener('keyup', () => {
  saveUrlNode.innerText = resourcesUrlNode.value
})
