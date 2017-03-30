<div class="container-fluid">
    <div class="row">
        <div class="col-md-6 col-md-offset-3 text-center">
            <h1>Pr0verter</h1>
            <br><br>
            <video controls width="100%" height="100%">
                <source src="http://pr0verter.de{$base_url}data/{$file}.mp4" type="video/mp4" />
            </video>
            <br><br>
            <form>
                <div class="input-group">
                    <input type="text" class="form-control"
                           value="http://pr0verter.de{$base_url}data/{$file}.mp4" placeholder="Ein Döner bitte" id="copy-input">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button" id="copy-button"
                                data-toggle="tooltip" data-placement="button"
                                title="Copy to Clipboard">
                            Copy
                        </button>
                    </span>
                </div>
            </form>
            <br>
            <br>
            <a href="{$base_url}download/{$file}" class="btn btn-danger">download</a>
            <br>
        </div>
    </div>
</div>
<script type="text/javascript">
    {literal}
        $(document).ready(function () {            
            var copyTextareaBtn = document.getElementById("copy-button");

            copyTextareaBtn.addEventListener('click', function (event) {
                var copyTextarea = document.getElementById("copy-input");
                copyTextarea.select();

                try {
                    document.execCommand('copy');
                    window.getSelection().removeAllRanges();
                } catch (err) {
                    console.log('Ohhh... uhhhhh... clipboard funtzt nicht');
                }

            });
        });
    {/literal}
</script>
