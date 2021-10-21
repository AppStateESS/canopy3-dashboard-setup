const usernameInput = document.getElementById('username-input')
const passwordInput = document.getElementById('password-input')
const databaseInput = document.getElementById('database-input')
const hostInput = document.getElementById('host-input')
const portInput = document.getElementById('port-input')
const driverInput = document.getElementById('driver-input')

const usernameConfig = document.getElementById('username-config')
const passwordConfig = document.getElementById('password-config')
const databaseConfig = document.getElementById('database-config')
const hostConfig = document.getElementById('host-config')
const portConfig = document.getElementById('port-config')
const driverConfig = document.getElementById('driver-config')

const usernameError = document.getElementById('username-error')
const databaseError = document.getElementById('database-error')
const passwordWarning = document.getElementById('password-warning')

const testConnection = document.getElementById('test-connection')
const saveButton = document.getElementById('save-button')
const dbForm = document.getElementById('db-form')

const connectionError = document.getElementById('connection-error')
const successfulTestBanner = document.getElementById('successful-test')

const httpRequest = new XMLHttpRequest()

const checkPassword = () => {
  if (passwordInput.value.length == 0) {
    passwordWarning.style['display'] = 'block'
  } else {
    passwordWarning.style['display'] = 'none'
  }
}

const hideSaveButton = () => {
  saveButton.style['display'] = 'none'
}

const showSaveButton = () => {
  saveButton.style['display'] = 'block'
}

const testFields = () => {
  if (usernameInput.value.length == 0 || databaseInput.value.length == 0) {
    testConnection.disabled = true
  } else {
    testConnection.disabled = false
  }
}

saveButton.addEventListener('click', () => {
  console.log('save file')
})

dbForm.addEventListener('keydown', (e) => {
  if (e.keyCode == 13) {
    e.preventDefault()
    return false
  }
})

hostConfig.innerText = 'localhost'
driverConfig.innerText = driverInput.selectedOptions[0].value

usernameInput.addEventListener('keyup', () => {
  usernameConfig.innerText = usernameInput.value
})

usernameInput.addEventListener('blur', testFields)
usernameInput.addEventListener('mouseleave', testFields())

databaseInput.addEventListener('blur', testFields())
databaseInput.addEventListener('mouseleave', testFields())

passwordInput.addEventListener('blur', checkPassword)

passwordInput.addEventListener('keyup', () => {
  passwordConfig.innerText = passwordInput.value
})
databaseInput.addEventListener('keyup', () => {
  databaseConfig.innerText = databaseInput.value
})
hostInput.addEventListener('keyup', () => {
  hostConfig.innerText = hostInput.value
})
portInput.addEventListener('keyup', () => {
  portConfig.innerText = portInput.value
})
driverInput.addEventListener('change', () => {
  driverConfig.innerText = driverInput.value
})

testConnection.addEventListener('click', () => {
  clearTestFailed()
  connect()
})

const connect = () => {
  if (!httpRequest) {
    alert('Giving up :( Cannot create an XMLHTTP instance')
    return false
  }

  let requestUrl = new URL(window.location.href.replace(/view/, 'dbTest'))

  requestUrl.searchParams.set('username', usernameInput.value)
  requestUrl.searchParams.set('password', passwordInput.value)
  requestUrl.searchParams.set('dbname', databaseInput.value)
  requestUrl.searchParams.set('host', hostInput.value)
  requestUrl.searchParams.set('port', portInput.value)
  requestUrl.searchParams.set('driver', driverInput.value)

  httpRequest.onload = () => {
    if (httpRequest.status == 200) {
      parseResponse(httpRequest.response)
    } else {
      connectionError.innerHTML =
        '<span>Could not connect with these server.</span>'
      connectionError.style['display'] = 'block'
    }
  }
  httpRequest.open('GET', requestUrl)
  httpRequest.responseType = 'json'
  httpRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest')
  httpRequest.send()
}

const testSuccessful = () => {
  testConnection.style['display'] = 'none'
  successfulTestBanner.style['display'] = 'block'
  showSaveButton()
}

const testFailed = (message) => {
  connectionError.innerHTML =
    '<span><strong>Could not connect with these settings:</strong><br >' +
    message +
    '</span>'
  connectionError.style['display'] = 'block'
}

const clearTestFailed = () => {
  connectionError.style['display'] = 'none'
}

const parseResponse = (response) => {
  if (response.success) {
    testSuccessful()
  } else {
    hideSaveButton()
    if (response.error.databaseNameEmpty !== undefined) {
      databaseError.style['display'] = 'block'
      databaseError.innerHTML = 'Error: Database name empty'
    } else {
      databaseError.style['display'] = 'none'
    }

    if (response.error.userNameEmpty !== undefined) {
      usernameError.style['display'] = 'block'
      usernameError.innerHTML = 'Error: User name empty'
    } else {
      usernameError.style['display'] = 'none'
    }

    if (response.error.connection !== undefined) {
      testFailed(response.error.connection)
    }
  }
}
