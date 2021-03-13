function toggleText(self, str, toggle = 'Cancel') {
  self.innerText = self.innerText === str ? toggle : str;
}

function clearAlert()
{
  const alert = bootstrap.Alert.getInstance(document.querySelector('.alert'));
  if (alert)
    alert.close();
}

function openAlert(message, parent = null)
{
  let container = document.getElementById('alert');

  if (parent !== null)
    container = parent.querySelector('#alert');

  if (!container)
    return;
  
  container.innerHTML = "";

  const template = `
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
      <strong>Oopps! Something went wrong.</strong> 
      <p class="m-0">${message}</p>
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  `;

  container.innerHTML += template;
  container.scrollIntoViewIfNeeded();
  new bootstrap.Alert(document.querySelector('.alert'));
}

function closeAlert()
{
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(alert => alert.remove());
}

function error(err) 
{
  if (err.response) {
    openAlert(err.response.data.error.description);
  }
}

function showError(errors, parent = null)
{
  if (errors.length == 0)
    return;
  
  openAlert(errors[0], parent);
}

function handleError(err)
{
  if (err.response)
    console.log(err.response.data.error.description);
  else
    console.log(err);
}

function toast(message) 
{
  clearToasts();

  let template = `
    <div class="toast" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header">
        <strong class="mr-auto">Notification</strong>
        <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="toast-body">
        ${message}
      </div>
    </div>
  `;
  let container = document.querySelector('.toast-container');
  container.innerHTML += template;

  initToasts();
}

function clearToasts()
{
  let container = document.querySelector('.toast-container');
  container.innerHTML = '';
}

function delay(fn, ms) 
{
  let timer = 0;
  return function(...args) 
  {
    clearTimeout(timer);
    timer = setTimeout(fn.bind(this, ...args), ms || 0);
  };
}

// API
let response = [];
let page = 1;
let maxTry = 2;
let tries = 0;
let fetching = false;
let scrolling = false;

/**
 * Create api request boilerplate
 *
 * @param string url - The url endpoint of the api
 * @param object data - The data to be sent to the api
 * @returns Promise
 */
async function create(url, data) 
{
  try {
    const res = await axios.post(url, data);

    // Toast the message
    toast(res.data.data.message);
    
    return await res.data.data.sub;
  }
  catch(err) {
    error(err);
    return Promise.reject(err)
  }
}

/**
 * Fetch api request boilerplate
 *
 * @param string url - The endpoint of the api
 * @param node parent - The parent where to put the no item
 * @param function success - Callable function after the request
 * @param boolean append - appends the result or not
 */
async function fetch(url, parent, success)
{
  page = 1;
  tries = 0;

  if (fetching)
    return;
  
  if (!fetching)
    fetching = true;

  // Shows the loading screen
  showLoading();
  try {
    const res = await axios.get(url);
    const data = await res.data.data.sub;
    response = data;

    hideLoading();

    if (parent !== null) {
      // Removes the current items
      masonry.remove(masonry.getItemElements());
      // Triggers update to the ui
      masonry.layout();
    }

    data.forEach((v, i) => {
      setTimeout(() => success(v), i * 100);
    });
  }
  catch (err) {
    handleError(err);
  }

  // If there is no response
  if (response.length === 0)
    addNoItem(parent);
  
  // Hides the loading, if the promise does not fulfilled
  hideLoading();

  // After the request, set it to false
  fetching = false;
}

/**
 * Fetch API boilerplate for scrolling
 *
 * @param string url
 * @param function success
 */
async function scroll(url, success)
{
  if (scrolling || tries >= maxTry)
    return;

  if (!scrolling)
    scrolling = true;

  try {
    const res = await axios.get(`${url}&offset=${page}`);
    const items = await res.data.data.sub;

    if (items.length === 0) {
      let tmp = page - 1;
      page = tmp < 1 ? 1 : tmp;
      tries++;
    } 

    items.forEach((v, i) => {
      response.push(v);
      setTimeout(() => success(v), i * 100);
    });
  }
  catch(err) {
    handleError(err);
  }

  page++;
  scrolling = false;
}

/**
 * Update api request boilerplate
 *
 * @param string url - The url endpoint of the api
 * @param object data - The data to be sent to the api
 * @return Promise
 */
async function update(url, data)
{
  try {
    const res = await axios.patch(url, data);

    // Toast the message
    toast(res.data.data.message);

    return await res.data.data.sub;
  }
  catch(err) {
    error(err);
    return Promise.reject(err);
  }
}

/**
 * Delete api request boilerplate
 *
 * @param string url - The url endpoint of the api
 * @param object data - The data to be sent to the api
 * @return Promise
 */
async function remove(url, data)
{
  try {
    const res = await axios.delete(url, { data });
    
    // Toast the message
    toast(res.data.data.message);

    return await res.data.data.sub;
  }
  catch(err) {
    handleError(err);
    return Promise.reject(err);
  }
}

/**
 * Adds the no item label to the UI, if there were no item fetched
 *
 * @param node parent - The node element where to put the element
 */
function addNoItem(parent)
{
  if (parent === null)
    return;

  let template = `
    <div class="no-item text-center p-5">
      <h4>No data available</h4>
    </div>
  `;

  parent.innerHTML = template;
}

/**
 * Shows the loading screen
 */
function showLoading()
{
  let loading = document.querySelector('.loading');
  if (!loading)
    return;

  // parent.classList.add('d-none');
  loading.classList.remove('d-none');
}

/**
 * Hides the loading screen
 */
function hideLoading()
{
  let loading = document.querySelector('.loading');
  if (!loading)
    return;

  // parent.classList.remove('d-none');
  loading.classList.add('d-none');
}