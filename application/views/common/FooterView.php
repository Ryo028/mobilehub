<script type="text/javascript">
    /* important to locate this script AFTER the closing form element, so form object is loaded in DOM before setup is called */
    function search() {
        var searchQuery = $("#search-term").val();
        searchQuery = searchQuery.replace(/ /g, '+');
        window.location.href = '/MobileHub/index.php/search?query='+searchQuery;
    }

    $.validate({
        modules: 'date, security',
        onSuccess: function() {
            $dataToSend = new Array();
            $loginForm = $("#loginForm");
            $serializedData = $loginForm.serializeArray();
            $tmpObj1 = $serializedData[0];
            $tmpObj2 = $serializedData[0];
            if (!($tmpObj1.value === '' || $tmpObj2.value === '')) {
                $.post("/MobileHub/index.php/api/auth/login", $serializedData, function(content) {

                    // Deserialise the JSON
                    content = jQuery.parseJSON(content);
                    if (content.message === "correct") {
                        $("#error").removeClass('alert alert-danger');
                        $("#error").addClass('alert alert-success');
                        $("#error").text('Login successful!');
                        location.reload();
                    } else {
                        $("#error").addClass('alert alert-danger');
                        $("#error").text('Sorry, your login credentials are incorrect! Please try again');
                    }
                }), "json";
                return true;
            }
        }
    });

    function resetForm() {
        $("#error").removeClass('alert alert-danger');
        $("#error").text('');
    }

</script>
<hr>
<footer>
    <p style="text-align: center">&copy; Created by Sahan Serasinghe | 2013</p>
</footer>
<script src="<?php echo site_url('../resources/js/bootstrap.min.js') ?>"></script>
</body>
</html>
