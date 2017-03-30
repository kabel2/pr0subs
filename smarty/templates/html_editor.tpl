<div class="container-fluid">
    <div class="col-md-3 col-md-offset-4 text-center">
        <h1>Editor</h1>
    </div>
    <br>
    <br>
    <br>
    <div class="row">
        <div class="col-md-6">
            <div id="container" align="center" text-center>
                <video loop width="100%" height="100%">
                    <source src="http://pr0verter.de{$base_url}data/{$file}.mp4" type="video/mp4"/>
                    <track label="Captions" kind="captions" srclang="de" src="http://pr0verter.de/subs/data/test.vtt" id="video_subs" default>
                </video> 
                <style type="text/css" id="extra-textarea-styles"></style>
            </div>
            <br>
            <br>
            <br>
            <div id="shortcuts">
                <table class="table">
                    <p> shortcuts </p>
                    <tbody>
                        <tr>
                            <td>Space</td>
                            <td>Pause/Start</td>
                        </tr>
                        <tr>
                            <td>A</td>
                            <td>gedrückt halten solange Untertitel erwünscht</td>
                        </tr>
                        <tr>
                            <td>Pfeiltasten Rechts/Links</td>
                            <td>vorspulen, zurückspulen</td>
                        </tr>
                        <tr>
                            <td>entf.</td>
                            <td>ausgewählten Untertitel löschen</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-md-6">
            <div class="panel panel-default" id="sublist">
                <div class="panel-heading">Subtitel</div>
                <table class="table" id="subtable">
                    <thead>
                    <th> Start </th>
                    <th> Ende </th>
                    <th> Text </th>
                    </thead>
                    <tbody> 

                    </tbody>
                </table>
            </div>
            <br>
            <br>
            <div text-center>
                <h2> Untertitel Größe </h2>
                <div class="input-group">
                    <input type="number" id="font_size" name="font_size" min="3" max="100" value="30" class="form-control" />
                    <div class="input-group-addon">px</div>
                </div>
                <br>
                <hr>
                <br>
                <h2>Größe die das Video haben soll in MB:</h2>
                <div class="input-group">
                    <div class="input-group-addon">1 - 30</div>
                    <input type="number" id="limit" name="limit" min="3" max="30" value="6" class="form-control" />
                    <div class="input-group-addon">MB</div>
                </div>
                <br>
                <hr>
                <br>
                <h2> TON????? </h2>
                <div class="checkbox">
                    <label>
                        <input id="sound" name="sound" type="checkbox"> JA!
                    </label>
                </div>
                <br>
                <hr>
                <br>
                <h2> Video Auflösung </h2>
                <select id="videoResolution" class="form-control">
                    <option>Automatisch</option>
                    <option>nix machen/beibehalten</option>
                    <option>720p</option>
                    <option>480p</option>
                    <option>360p</option>
                </select>
                <br>
                <input id="save" action="getSubs" class="btn btn-danger" type="submit" value="blaze it">
            </div>
        </div>
    </div>
</div>
<div id="go_status" style="display: none;"></div>                    
<br>
