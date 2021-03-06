<div class="container">
    <div class="row">
        <h4>Your Answer</h4>
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">                
                    <form accept-charset="UTF-8" action="" method="POST">
                        <textarea class="form-control" name="message" placeholder="Type in your message" rows="5" style="margin-bottom:10px;" id="ansDesc" maxlength="600"></textarea>
                        <button class="btn btn-info pull-right" type="submit" onclick="postAnswer();">Post New Answer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
                            function postAnswer()
                            {
                                $questionId = "<?php echo $questionId ?>";
                                $qDesc = $("#ansDesc").val();
                                $tutorName = "<?php echo $name ?>";
                                $jsonObj = {"questionId": $questionId, "description": $qDesc, "username": $tutorName};

                                if ($qDesc !== '') {
                                    $.post("/MobileHub/index.php/api/answer/post/", $jsonObj, function(content) {

                                        // Deserialise the JSON
                                        content = jQuery.parseJSON(content);
                                        console.log(content);
                                        if (content.message === "Success") {
                                            location.reload();
                                        } else {
                                            $('#errModalBody').html("<p><center>" + content.type + "</center></p>");
                                            $('#errorModal').modal('show');
                                        }
                                    }).fail(function() {
                                        $('#errModalBody').html("<p><center>Oops! something went wrong! Please try again</center></p>");
                                        $('#errorModal').modal('show');
                                    }), "json";
                                    return true;
                                } else {
                                    $('#errModalBody').html("<p><center>" + "Please enter something before posting an answer!" + "</center></p>");
                                    $('#errorModal').modal('show');
                                }
                            }
</script>