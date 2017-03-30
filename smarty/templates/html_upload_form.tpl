<div class="container-fluid">
    <div class="row">
        <div class="col-md-6 col-md-offset-3 text-center">
            <h1>Untertitel</h1><br>
            beTA<br>
            <br><br>
            <form action="{$base_url}upload" method="POST" id="upload_form" enctype="multipart/form-data">
                <hr>
                <div class="form-group">
                    <h2>Datei:</h2>
                    <label class="btn btn-default btn-file">
                        wählen <input type="file" class="form-control" name="file" id="file" style="display: none;" />
                    </label>
                </div>
                <br>
                <h3>ODER URL ANGEBEN</h3>
                <br>
                <div class="form-group">
                    <h2>URL:</h2>
                    <input type="text" class="form-control" size=30 name="url" id="url" />
                </div>
                <br>
                <input class="btn btn-danger" type="submit" value="uppen">
                <br>
                <br>
            </form>
            
        </div>

    </div>

</div>

<div class="container-fluid" id="full">
    <div class="row">
        <div class="col-md-6 col-md-offset-3 text-center" id="progress">
            <h2>lade hoch ...</h2>
            <br>
            <div class="progress">
                <div id="upload_bar" class="progress-bar progress-bar-danger progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0;">
                    0%
                </div>
            </div>
        </div>
    </div>
</div>

<div id="status" style="display: none;"></div>





