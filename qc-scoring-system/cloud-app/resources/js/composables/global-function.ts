
export const startCountdownInterval = (token: string, key: any, setCountdown: any, setKey: any) => {
     setCountdown("1m59s");
     if (key) {
          clearInterval(key);
     }
     const intervalDuration = parseInt(
          window.localStorage.getItem(`otp-interval-second-${token}`) ||
          "120"
     );
     const canResendOtpAt = new Date();
     canResendOtpAt.setSeconds(canResendOtpAt.getSeconds() + intervalDuration);

     setKey(
          setInterval(() => {
               const currentTime = new Date();
               const timeLeft = canResendOtpAt.getTime() - currentTime.getTime();
               const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
               const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
               window.localStorage.setItem(
                    `otp-interval-second-${token}`,
                    (minutes * 60 + seconds).toString()
               );
               if (timeLeft > 0) {
                    setCountdown(`${minutes}m${seconds}s`);
               } else {
                    window.localStorage.removeItem(
                         `otp-interval-second-${token}`
                    );
                    setCountdown('')
                    clearInterval(key);
               }
          }, 1000)
     )
};



export const isNumber = (evt: any) => {
     if (evt.target.value.length >= 1) {
          evt.preventDefault()
     }
     const charCode = evt.which ? evt.which : evt.keyCode;
     if (
          charCode > 31 &&
          (charCode < 48 || charCode > 57) &&
          [46, 47, 44, 43, 45, 101].includes(charCode)
     )
          evt.preventDefault();
     return true;

     
};

export const isInputNumber = (evt: any) => {
    const charCode = evt.which ? evt.which : evt.keyCode;
    if ([46, 44, 43, 45, 101].includes(charCode)) {
        evt.preventDefault();
    }
    if (
        charCode > 31 &&
        (charCode < 48 || charCode > 57) &&
        charCode !== 46
    ) {
        evt.preventDefault();
    }
    return true;
};

export const disableCopyPaste = (event: any) => {
     event.preventDefault();
};


export const showAlert = (message: string, type = 'warning') => {
     let color = 'bg-online'
     if (type === 'warning') color = 'bg-offline'

     const alert = document.getElementById('alert-flash-message')
     if (alert) {
          const messageContainer = alert.querySelector('.message');
          if (messageContainer) {
               messageContainer.innerHTML = message
               alert.classList.add(color)
               alert.classList.remove('hidden')
               alert.classList.add('flex')

               setTimeout(() => {
                    messageContainer.innerHTML = ''
                    alert.classList.remove('flex')
                    alert.classList.add('hidden')
                    alert.classList.remove(color)
               }, 3000)
          }

     }
}

export const randomString = (length = 10) => {
     let result = '';
     const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
     const charactersLength = characters.length;
     let counter = 0;
     while (counter < length) {
          result += characters.charAt(Math.floor(Math.random() * charactersLength));
          counter += 1;
     }
     return result;
}

export const compressImage = (file: any, maxWidth: any, maxHeight: any, outputFormat: any, quality: any, callback: any) => {
     const reader = new FileReader();
     reader.onload = function (event: any) {
          const img: any = new Image();

          img.onload = function () {
               // Calculate the new size to fit within the specified dimensions while maintaining the aspect ratio.
               let width = img.width;
               let height = img.height;
               if (width > maxWidth) {
                    height *= maxWidth / width;
                    width = maxWidth;
               }
               if (height > maxHeight) {
                    width *= maxHeight / height;
                    height = maxHeight;
               }

               // Create a canvas with the new size
               const canvas = document.createElement('canvas');
               canvas.width = width;
               canvas.height = height;

               // Draw the image on the canvas
               const ctx: any = canvas.getContext('2d');
               ctx.drawImage(img, 0, 0, width, height);

               // Convert the canvas to a data URL with the specified output format and quality
               const compressedImageData = canvas.toDataURL(`image/${outputFormat}`, quality);

               // Call the callback function with the compressed image data URL
               callback(compressedImageData);
          };

          img.src = event.target.result;
     };
     reader.readAsDataURL(file);
}

export const routeAppendParam = (params: any, silent = true) => {
     const newUrl = new URL(window.location.href);
     for (const key in params) {
          newUrl.searchParams.set(key, params[key]);
     }
     window.history.pushState({}, '', newUrl);
     if (!silent) {
          window.dispatchEvent(new Event('changeUrlParameter'));
     }
}


export const getQueryParam = (key: string) => {
     return new URLSearchParams(window.location.search).get(key) || null
}

export const getAllQueryParameter = () => {
     const entries = new URL(window.location.href).searchParams.entries();
     const result: any = {}
     for (const [key, value] of entries) {
          result[key] = value;
     }
     return result;
}
export const getAllQueryParamToPost = () => {
     const params = new URL(window.location.href).searchParams;
     const result: any = {};

     for (const [key, value] of params.entries()) {
          const match = key.match(/^([^\[]+)\[([^\]]+)\]$/);

          if (match) {
               const parent = match[1]; // e.g. "filter"
               const child = match[2];  // e.g. "date_start"

               if (!result[parent]) result[parent] = {};
               result[parent][child] = value;
          } else {
               result[key] = value;
          }
     }

     return result;
}


export const removeURLParameter = (params: any, silent = true) => {
     const url = new URL(window.location.href);
     for (const key in params) {
          url.searchParams.delete(params[key]);
     }
     window.history.replaceState({}, document.title, url.toString());
     if (!silent) {
          window.dispatchEvent(new Event('changeUrlParameter'));
     }
}
export const removeAllUrlParameter = (silent = true) => {
     const url = new URL(window.location.href);
     window.history.replaceState({}, document.title, `${url.origin}${url.pathname}`);
     if (!silent) {
          window.dispatchEvent(new Event('changeUrlParameter'));
          window.dispatchEvent(new Event('removeAllUrlParameter'));
     }
}

export const validateMinimumDateRange = (start: string, end: string, validate = 30) => {
     return true;
     if (start && end) {
          const startDate = new Date(start);
          const endDate = new Date(end);
          if (endDate < startDate) {
               showAlert('The start date must be greater than the end date')
               return false;
          }

          const Difference_In_Time = endDate.getTime() - startDate.getTime();
          const Difference_In_Days = Difference_In_Time / (1000 * 3600 * 24);
          if (Difference_In_Days > validate) {
               showAlert(`Maximum range date is ${validate}`)
               return false;
          }

     }

     return true;
}


export const updateUserHeaderValue = (key: string, value: string) => {
     if (key == 'profile') {
          const element = document.getElementById('header-user-profile');
          if (element) {
               element.setAttribute('src', value)
          }
     }
     if (key == 'name') {
          const element = document.getElementById('header-user-name')
          if (element) {
               element.innerHTML = value
          }
     }
}

export const isEmptyValue = (value: string) => {
     if (!value) {
          return true
     }
     return value.toString().replace(/ /g, '') == ''
}

export const ASSET = (path: string) => {
     const baseUrl = document.querySelector("meta[name='app-url']")?.getAttribute('content')
     return `${baseUrl}${path}`
}
