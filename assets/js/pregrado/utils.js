
const downloadAction = (filename, data) => {
  let pom = document.createElement('a');
  pom.setAttribute('href', 'data:text/csv;charset=utf-8,' + encodeURIComponent(data));
  pom.setAttribute('download', filename);

  if (document.createEvent) {
    let event = document.createEvent('MouseEvents');
    event.initEvent('click', true, true);
    pom.dispatchEvent(event);
  } else {
    pom.click();
  }

}

const clickAction = (url) => {
  let pom = document.createElement('a');
  pom.setAttribute('href', url);

  if (document.createEvent) {
    let event = document.createEvent('MouseEvents');
    event.initEvent('click', true, true);
    pom.dispatchEvent(event);
  } else {
    pom.click();
  }
}

export {downloadAction, clickAction}