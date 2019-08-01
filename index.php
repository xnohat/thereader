<!DOCTYPE html>
<html>

<head>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta content="IE=edge" http-equiv="X-UA-Compatible">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>TheReader - Book Text to Speech by Google</title>
    <meta name="description" content="Đọc bất kỳ file pdf hay epub nào, một cách trơn tru bằng công nghệ Text to Speech của Google, api hacked by xnohat">
    <meta property="og:title" content="TheReader - Book Text to Speech by Google" />
    <meta property="og:image" content="http://www.techsignin.com/wp-content/uploads/2017/01/google-translate-logo.png" />
    <meta property="og:site_name" content="TheReader - Book Text to Speech by Google" />
    <meta property="og:description" content="Đọc bất kỳ file pdf hay epub nào, một cách trơn tru bằng công nghệ Text to Speech của Google, api hacked by xnohat" />

    <style type="text/css">
        /* Desktop screen */
        @media all and (min-width: 901px) {
            .editor {
                width: 45%;
                margin-left: auto;
                margin-right: auto;
            }

            #ta {
                width: 100%;
            }

            #btnread {
                width: 100%;
            }

            #url {
                width: 70%;
            }
        }

        /* tablet screen */
        @media all and (max-width: 900px) and (min-width: 767px) {
            .editor {
                width: 45%;
                margin-left: auto;
                margin-right: auto;
            }

            #ta {
                width: 100%;
            }

            #btnread {
                width: 100%;
            }

            #url {
                width: 70%;
            }
        }

        /* mobile screen */
        @media all and (max-width: 766px) {
            #ta {
                width: 100%;
            }

            #btnread {
                width: 100%;
            }

            #url {
                width: 70%;
            }
        }
    </style>
    <script src="jquery-3.1.1.min.js"></script>
    <script src="fingerprint2.min.js"></script>
</head>

<body>

    <div class="editor">
        <center>
            <p>
                <form id="form" action="uploadbook.php" method="post" enctype="multipart/form-data">
                    <label for="filebook">Upload Book (PDF,EPUB)</label>
                    <i>max file size <?php echo isa_convert_bytes_to_specified(file_upload_max_size(),'M'); ?> MB</i>
                    <input id="filebook" type="file" accept=".pdf,.epub" name="book" />
                    <input class="btn btn-success" type="button" value="Upload" onclick="douploadbook()" />
                </form>
            </p>
            <P id='uploadstatus'></P>
            <p>
                <label for="currentpage">Get text from page: </label>
                <input id="currentpage" type="text" size=3 />
                <input id="btngettext" type="button" value="Get Text" onclick="gettextfrompage()" />
            </p>
            <textarea id="ta" rows="20" spellcheck="false" placeholder="Chọn file PDF, Epub để upload (Windows,Mac,iOS,Android đều được hỗ trợ), nhập trang cần đọc và bấm nút Get Text để load nội dung text của trang, chọn ngôn ngữ của sách, rồi chọn tốc độ đọc, rồi bấm Read. Đọc hết trang sẽ tự động chuyển trang. Please choose PDF, Epub file to upload (Windows,Mac,iOS,Android supported), set Page want to read, click Get Text to load text from page, choose Language of book, select reading speed, Click Read to start, next page will automatic load after current page completed"></textarea>
            <p id="ta-log"></p>
            <p id="msg-disablescreenlock" hidden><i>Please Turn of Auto Screen Lock of your mobile device</i></p>
            <p>
                <select id="lang">
                    <option value="vi">Vietnamese</option>
                    <option value="En-gb">English</option>
                </select>
            </p>
            <div id="speedcontrol">
                <p>
                    <p>Reading speed: <b><span id="selectedspeed">1.2</span>x</b></p>
                    <input type="range" id="speed" value="1.2" min="1" max="2" step="0.1" oninput="getspeed()" onchange="getspeed()">
                </p>
            </div>
            <input id="btnread" type="button" value="Read" onclick="googletts()" />
            <p><audio autoplay controls src="" id="reader" hidden></audio></p>
        </center>
    </div>

    <script type="text/javascript">
        // Detect Mobile Browser
        function isMobile() {
            /*var a = navigator.userAgent || navigator.vendor || window.opera;
            if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(a) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0, 4))) { return true; }
            return false;*/
            if ((typeof window.orientation !== "undefined") || (navigator.userAgent.indexOf('IEMobile') !== -1)) {
                return true;
            }
            return false;
        }

        //Safari detect
        var isSafari;
        if (((navigator.userAgent.indexOf('Safari') != -1) || (navigator.userAgent.indexOf('FBIOS') != -1) || (navigator.userAgent.indexOf('MessengerForiOS') != -1)) && navigator.userAgent.indexOf('Chrome') == -1) {
            isSafari = true
        } else {
            isSafari = false
        };

        //Disable speed control on Safari
        /*if (isSafari == true) {
            $('#speedcontrol').hide();
        }*/

        //Disable zoom in iOS 10
        document.documentElement.addEventListener('touchstart', function(event) {
            if (event.touches.length > 1) {
                event.preventDefault();
            }
        }, true);

        String.prototype.hashCode = function() {
            var hash = 0;
            if (this.length == 0) {
                return hash;
            }
            for (var i = 0; i < this.length; i++) {
                var char = this.charCodeAt(i);
                hash = ((hash << 5) - hash) + char;
                hash = hash & hash; // Convert to 32bit integer
            }
            return hash;
        }

        //UUID Generate from FingerPrint
        if (window.requestIdleCallback) {
            requestIdleCallback(function() {
                Fingerprint2.get(function(components) {
                    //console.log(components) // an array of components: {key: ..., value: ...}
                    //console.log(Fingerprint2.x64hash128(components.map(function (pair) { return pair.value }).join(), 31));
                    if (getCookie("uuid") === null) setCookie('uuid', Fingerprint2.x64hash128(components.map(function(pair) {
                        return pair.value
                    }).join(), 31), 365);
                })
            })
        } else {
            setTimeout(function() {
                Fingerprint2.get(function(components) {
                    //console.log(components) // an array of components: {key: ..., value: ...}
                    //console.log(Fingerprint2.x64hash128(components.map(function (pair) { return pair.value }).join(), 31));
                    if (getCookie("uuid") === null) setCookie('uuid', Fingerprint2.x64hash128(components.map(function(pair) {
                        return pair.value
                    }).join(), 31), 365);
                })
            }, 500)
        }

        if (isMobile()) {
            $('#msg-disablescreenlock').show();
        }
    </script>

    <script type="text/javascript">
        var current_play = 0; //Global Variable - cursor of current play file

        function googletts() {

            //remove any existed event listener
            var old_element = document.getElementById('reader');
            var new_element = old_element.cloneNode(true);
            old_element.parentNode.replaceChild(new_element, old_element);

            //Get Text
            text = $('#ta').val(); // Global variable - text

            if (text == '') {
                alert('Please enter text before press Read');
                return false;
            }

            var currentpage = document.getElementById('currentpage').value;
            read(currentpage); // read page
            console.log('reading page: ' + currentpage);
            $('#reader').show();

            document.getElementById('reader').addEventListener('ended', readnext); //when end current playing read please read next

        } //end function

        function read(page) {
            googlettsurl = 'tts.php?safari=' + isSafari + '&lang=' + document.getElementById("lang").value + '&speed=' + document.getElementById("speed").value + '&book=' + sessionStorage.getItem('tmp') + '&page=' + page + '&pages=' + sessionStorage.getItem('pages');

            $('#reader').attr('src', googlettsurl).get(0).play();

            if (isSafari == false) { // Not Safari
                document.getElementById('reader').playbackRate = document.getElementById("speed").value;
                document.getElementById('reader').defaultPlaybackRate = document.getElementById("speed").value;
            }
            //document.getElementById('ta-log').innerHTML = text; //current read
        }

        function readnext() {

            var originalhashcontent = document.getElementById('ta').value.hashCode();
            getnextpage(); // get next page content

            //loop every second to check new page content loaded ?
            var intervalCheckHash = setInterval(() => {
                console.log('originhash: ' + originalhashcontent + ' - currhash: ' + document.getElementById('ta').value.hashCode());
                if (originalhashcontent != document.getElementById('ta').value.hashCode()) { //new content loaded 
                    read(document.getElementById('currentpage').value); //read next page
                    clearInterval(intervalCheckHash); //clear interval
                    intervalCheckHash = 0; //destroy variable
                }
            }, 100);

            if (document.getElementById('currentpage').value == sessionStorage.getItem('pages')) {
                document.getElementById('reader').removeEventListener('ended', readnext); //remove event end of reader audio tag because its last page of book
                return;
            }

            //setTimeout(function () {  googletts(); }, 3000);
        }

        function getnextpage() {
            var currentpage = parseInt(document.getElementById('currentpage').value);
            if (currentpage != sessionStorage.getItem('pages')) { //if current page is not reach last page
                document.getElementById('currentpage').value = currentpage + 1;
                gettextfrompage();
            }
        }

        function getspeed() {
            var x = document.getElementById("speed").value;
            document.getElementById("selectedspeed").innerHTML = x;
            /*if (isSafari == false) { // Not Safari
                document.getElementById('reader').playbackRate = document.getElementById("speed").value;
                document.getElementById('reader').defaultPlaybackRate = document.getElementById("speed").value;
            }*/
        }

        function douploadbook() {

            //Lấy ra files
            var file_data = $('#filebook').prop('files')[0];
            if (file_data == undefined) {
                $('#uploadstatus').text('Please choose book file to upload');
                return;
            }
            //lấy ra kiểu file
            var type = file_data.type;
            //Xét kiểu file được upload
            var match = ["application/pdf", "application/epub+zip"];
            //kiểm tra kiểu file
            if (type == match[0] || type == match[1] || type == match[2]) {
                //khởi tạo đối tượng form data
                var form_data = new FormData();
                //thêm files vào trong form data
                form_data.append('file', file_data);
                //sử dụng ajax post
                $.ajax({
                    url: 'uploadbook.php', // gửi đến file upload.php 
                    dataType: 'text',
                    xhr: function() {
                        var myXhr = $.ajaxSettings.xhr();
                        if (myXhr.upload) {
                            myXhr.upload.addEventListener('progress', uploadprogress, false);
                        }
                        return myXhr;
                    },
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: form_data,
                    type: 'post',
                    success: function(response) {
                        var res = JSON.parse(response);
                        if (res.status == true) {
                            $('#uploadstatus').text('Book: ' + res.bookname + ' has: ' + res.pages + ' pages');

                            sessionStorage.setItem('book', res.bookname);
                            sessionStorage.setItem('tmp', res.booktmp);
                            sessionStorage.setItem('pages', res.pages);

                            $('#filebook').val('');
                        } else {
                            $('#uploadstatus').text(res.message);
                        }
                    }
                });
            } else {
                $('#uploadstatus').text('Only allow upload PDF or Epub');
                $('#filebook').val('');
            }
            return false;

        }

        function uploadprogress(e) {

            if (e.lengthComputable) {
                var max = e.total;
                var current = e.loaded;

                var Percentage = (current * 100) / max;
                //console.log(Math.round(Percentage) + '%');
                $('#uploadstatus').text('Uploading...' + Math.round(Percentage) + '%');

                if (Percentage >= 100) {
                    // upload process completed  
                }
            }
        }

        function gettextfrompage() {
            var currentpage = document.getElementById('currentpage').value;

            document.getElementById('btngettext').value = 'Loading...';

            $.ajax({

                type: 'GET',
                url: 'gettext.php?book=' + sessionStorage.getItem('tmp') + '&page=' + currentpage + '&pages=' + sessionStorage.getItem('pages'),
                data: {},
                success: function(data) {

                    document.getElementById('btngettext').value = 'Get Text';

                    $('#ta').html(data);

                },
                error: function() {
                    console.log('Cannot xhr request');
                    alert('Cannot get URL, press Retry Again');
                    document.getElementById('btngettext').value = 'Retry Again';
                },
                statusCode: {
                    429: function() {
                        alert('Rate limit exceeded: you are doing action too fast');
                        console.log('429 Rate limit exceeded');
                    },
                    500: function() {
                        alert('Server Error, please try again later')
                        console.log('500 Internal server error');
                        document.getElementById('btngettext').value = 'Retry Again';
                    },
                    401: function() {
                        console.log('401 Unauthorized');
                    }
                }

            });

        }

        //ECMA2017 Promise and Await Sleep
        //await sleep(2000);
        function sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }

        function setCookie(name, value, days) {
            var expires = "";
            if (days) {
                var date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "") + expires + "; path=/";
        }

        function getCookie(name) {
            var nameEQ = name + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }

        function eraseCookie(name) {
            document.cookie = name + '=; Max-Age=-99999999;';
        }
    </script>
</body>

</html>

<?php
// Returns a file size limit in bytes based on the PHP upload_max_filesize
// and post_max_size
function file_upload_max_size()
{
    static $max_size = -1;

    if ($max_size < 0) {
        // Start with post_max_size.
        $post_max_size = parse_size(ini_get('post_max_size'));
        if ($post_max_size > 0) {
            $max_size = $post_max_size;
        }

        // If upload_max_size is less, then reduce. Except if upload_max_size is
        // zero, which indicates no limit.
        $upload_max = parse_size(ini_get('upload_max_filesize'));
        if ($upload_max > 0 && $upload_max < $max_size) {
            $max_size = $upload_max;
        }
    }
    return $max_size;
}

function parse_size($size)
{
    $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
    $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
    if ($unit) {
        // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
        return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
    } else {
        return round($size);
    }
}

function isa_convert_bytes_to_specified($bytes, $to, $decimal_places = 1)
{
    $formulas = array(
        'K' => number_format($bytes / 1024, $decimal_places),
        'M' => number_format($bytes / 1048576, $decimal_places),
        'G' => number_format($bytes / 1073741824, $decimal_places)
    );
    return isset($formulas[$to]) ? $formulas[$to] : 0;
}
?>