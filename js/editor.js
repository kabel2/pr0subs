$(function () {
    $(document).ready(function () {
        if (/editor/i.test($(location).attr('pathname'))) {
            var height = Math.max($("#container").height(), $("#sublist").height());
            $("#container").height(height);
            $("#sublist").height(height);

            var video = $('video')[0];

            var down = false;
            var table = document.getElementById("subtable");
            var videoPressDownTime = 0;
            var selectedRow = 0;

            $('body').focusout(function (e) {
                if (/textform/i.test(e.target.id)) {
                    row = parseInt(e.target.id.split('-')[1]) - 1;
                    video.textTracks[0].cues[row].text = getContentOfRow((row + 1), "text");
                    console.log(video.textTracks);
                }
            });

            document.getElementById('font_size').addEventListener('change', function () {
                $('#extra-textarea-styles').html('::cue{font-size:' + $(this).val() + 'px}');
            });
            // ::cue{font-size:10px;}

            video.addEventListener("loadedmetadata", function () {
                track = document.createElement("track");
                track.kind = "captions";
                track.label = "German";
                track.srclang = "de";
                track.src = "http://pr0verter.de/subs/data/test.vtt";
                track.addEventListener("load", function () {
                    this.mode = "showing";
                    video.textTracks[0].mode = "showing"; // thanks Firefox 
                });
                this.appendChild(track);
            });

            function jumpToTime(time) {
                if (video.paused) {
                    video.play();
                    video.currentTime += time;
                } else {
                    video.currentTime += time;
                }
            }
            function setTime(id, time) {
                var num = Math.max((parseFloat(document.getElementById(id).textContent) + time).toFixed(1), 0);
                if (num % 1 === 0) { // isInteger wird von IE nicht unterstützt?
                    // sons wird der minus/plus button verschoben weil .0 fehlt
                    document.getElementById(id).textContent = num + ".0";
                } else {
                    document.getElementById(id).textContent = num;
                }
            }
            function highlight_row() {
                var table = document.getElementById('subtable');
                var cells = table.getElementsByTagName('td');

                for (var i = 0; i < cells.length; i++) {
                    var cell = cells[i];
                    cell.onclick = function () {
                        var rowId = this.parentNode.rowIndex;

                        var rowsNotSelected = table.getElementsByTagName('tr');
                        for (var row = 0; row < rowsNotSelected.length; row++) {
                            rowsNotSelected[row].style.backgroundColor = "";
                            rowsNotSelected[row].classList.remove('selected');
                        }
                        var rowSelected = table.getElementsByTagName('tr')[rowId];
                        rowSelected.style.backgroundColor = "grey";
                        rowSelected.className += " selected";
                        selectedRow = rowId;
                    };
                }
            }
            highlight_row();

            function getContentOfRow(row, type) {
                if (type === 'start') {
                    var element = document.getElementById("start-" + row);
                    if (element !== null) { // check if element exists
                        return parseFloat(document.getElementById("start-" + row).textContent);
                    }
                }
                if (type === 'stop') {
                    var element = document.getElementById("stop-" + row);
                    if (element !== null) {
                        return parseFloat(document.getElementById("stop-" + row).textContent);
                    }
                }
                if (type === 'text') {
                    return document.getElementById("textform-" + row).value;
                }
            }


            $(document).keydown(function (e) {

                if (String.fromCharCode(e.which) === 'A' && down === false && video.paused === false && $("input:focus").length === 0) {
                    down = true;
                    videoPressDownTime = video.currentTime;
                }
                if (e.which === 32 && $("input:focus").length === 0) { // 32=space
                    // input:focus.length = 0 : kp was das zurückgibt aber wenn 1 ist der cursor innem textfeld wenn 0 nicht
                    // stop/play video
                    e.preventDefault();
                    if (video.paused) {
                        video.play();

                    } else {
                        video.pause();
                    }
                    if (down) {
                        down = false;
                    }
                }
                if (e.which === 37 && $("input:focus").length === 0) { // arrow key left
                    jumpToTime(-3);
                }
                if (e.which === 39 && $("input:focus").length === 0) { // arrow key right
                    jumpToTime(3);
                }
                if (e.which === 46 && selectedRow !== 0) { // entf
                    table.deleteRow(selectedRow);
                    // todo: table(panel) length
                }
            });
            $(document).keyup(function (e) {
                if (String.fromCharCode(e.which) === 'A' && down) {
                    down = false;
                    video.pause();
                    var row = table.insertRow(table.rows.length);
                    row.id = "row" + table.rows.length;
                    // style.height liefert "200px"
                    var height = parseInt(document.getElementById("sublist").style.height.split("p")[0]);

                    // wenn nicht genügend "platz" im table vorhanden ist wird die liste um 66px( eine zeilenbreite ) erweitert
                    if ((height - table.rows.length * 66) <= 0) {
                        document.getElementById("sublist").style.height = height + 66 + "px";
                    }

                    var cell1 = row.insertCell(0);
                    var cell2 = row.insertCell(1);
                    var cell3 = row.insertCell(2);
                    videoPressDownTime = videoPressDownTime.toFixed(1);
                    var current = video.currentTime.toFixed(1);
                    var rows = table.rows.length;
                    var startId = "start-" + rows;
                    var stopId = "stop-" + rows;
                    var textform = "textform-" + rows;
                    var buttonstartminus = "buttonstartminus-" + rows;
                    var buttonstartplus = "buttonstartplus- " + rows;
                    var buttonstopminus = "buttonstopminus-" + rows;
                    var buttonstopplus = "buttonstopplus-" + rows;

                    cell1.innerHTML = "<button id='" + buttonstartminus + "' type='button' class='btn btn-default btn-xs'>\n\
                                       <span class='glyphicon glyphicon-minus' aria-hidden='true'></span> \n\
                                       </button> <span id=" + startId + ">" + videoPressDownTime + "</span><button id='" + buttonstartplus + "' type='button' class='btn btn-default btn-xs'>\n\
                                       <span class='glyphicon glyphicon-plus' aria-hidden='true'></span> \n\
                                       </button>";

                    cell2.innerHTML = "<button id='" + buttonstopminus + "' type='button' class='btn btn-default btn-xs'>\n\
                                       <span class='glyphicon glyphicon-minus' aria-hidden='true'></span> \n\
                                       </button> <span id=" + stopId + ">" + current + " </span> <button id='" + buttonstopplus + "' type='button' class='btn btn-default btn-xs'>\n\
                                       <span class='glyphicon glyphicon-plus' aria-hidden='true'></span> \n\
                                       </button>";
                    cell3.innerHTML = "<div class='form-group'>\n\
                                       <input type='text' class='form-control' id='" + textform + "' autofocus>\n\
                                        </div>";

                    document.getElementById(buttonstartminus).addEventListener("click", function () {
                        setTime(startId, -0.1);
                    });
                    document.getElementById(buttonstartplus).addEventListener("click", function () {
                        setTime(startId, 0.1);
                    });
                    document.getElementById(buttonstopminus).addEventListener("click", function () {
                        setTime(stopId, -0.1);
                    });
                    document.getElementById(buttonstopplus).addEventListener("click", function () {
                        setTime(stopId, 0.1);
                    });
                    highlight_row();
                    track = video.textTracks[0];
                    track.addCue(new VTTCue(videoPressDownTime, current, ""));
                    console.log(track.cues);
                }
            });


            function createJSON() {
                // subs font size : integer
                // video size: int
                // sound on: string
                // video resolution: string
                // first 

                var object = [(table.rows.length - 1)];

                var objectContainsInfos = {
                    fontSize: document.getElementById("font_size").value,
                    targetVideoSize: parseFloat(document.getElementById("limit").value),
                    sound: document.getElementById("sound").value,
                    videoResolution: document.getElementById("videoResolution").value,
                    file: $(location).attr('pathname').split("/")[$(location).attr('pathname').split("/").length - 1]
                };

                for (var i = 1; i < table.rows.length; i++) {
                    var updatedRow = i;
                    updatedRow += 1;
                    var pObject = {
                        id: (i - 1),
                        start: getContentOfRow(updatedRow, "start"),
                        end: getContentOfRow(updatedRow, "stop"),
                        text: getContentOfRow(updatedRow, "text")
                    };
                    object[(i - 1)] = pObject;
                }
                var objectContainingAll = {0: objectContainsInfos, 1: object};
                return JSON.stringify(objectContainingAll);
            }
            function sendJSON(jsonString) {
                $.ajax({
                    type: 'POST',
                    url: 'http://pr0verter.de/subs/sendJson',
                    data: {jsonData: jsonString},
                    //dataType: 'json',
                    success: function (data) {
                        //document.location.href = "http://pr0verter.de/subs/status/" + data['file'];
                        console.log(data);
                        $('#go_status').html(data);
                    },
                    error: function (error) {
                        console.log(error);
                    }
                });
            }
            $('#save').click(function () {
                sendJSON(createJSON());
                $('#save').prop('disabled', true);
            });
        }
    });
});