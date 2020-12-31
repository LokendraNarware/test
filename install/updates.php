<?php

require "../includes/init.php";

if (isset($_POST['go'])) {
    $automatic_updates = (isset($_POST['automatic-updates']) && $_POST['automatic-updates'] == "On") ? "On" : "Off";
    $automatic_updates_purchased = (isset($_POST['automatic-updates-purchased']) && $_POST['automatic-updates-purchased'] == "On") ? "On" : "Off";
    $automatic_updates_backup = (isset($_POST['automatic-updates-backup']) && $_POST['automatic-updates-backup'] == "On") ? "On" : "Off";
    $send_errors = (isset($_POST['send-errors']) && $_POST['send-errors'] == "On") ? "On" : "Off";
    $send_usage_info = (isset($_POST['send-usage-info']) && $_POST['send-usage-info'] == "On") ? "On" : "Off";

    $studio->setopt("automatic-updates", $automatic_updates);
    $studio->setopt("automatic-updates-purchased", $automatic_updates_purchased);
    $studio->setopt("automatic-updates-backup", $automatic_updates_backup);
    $studio->setopt("send-errors", $send_errors);
    $studio->setopt("send-usage-info", $send_usage_info);

    header("Location: cron.php");
    die;
}

$error = "";
$studio->checkUpdates();

if (!$api->isAuthorized()) {
    $error = "Cannot install updates because this copy is not activated.";
}

$q = $studio->sql->query("SELECT * FROM updates WHERE updateStatus <> 1 ORDER BY id DESC");
$q2 = $studio->sql->query("SELECT * FROM plugins WHERE update_available != ''");

$updates = [];
$items = [];

$rows = $q->num_rows + $q2->num_rows;

?>
<!DOCTYPE HTML>
<html lang="en">
<head>
    <title>SEO Studio Installer</title>

    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700" rel="stylesheet">
    <style>
        body{background-color:#2196F3;font-family:Roboto,sans-serif;font-size:16px;color:#555;padding:70px 20px 0}.panel{background-color:#fafafa;border-radius:3px;-webkit-border-radius:3px;-moz-border-radius:3px;width:950px;margin:0 auto;box-shadow:2px 2px 4px rgba(0,0,0,.3)}.panel>table{width:100%}.logo{padding:25px 0 15px 30px}table tr,table tr td{margin:0;padding:0}.panel>table>tbody>tr>td:nth-child(2){background-color:#fff;padding:30px 35px}nav ul{margin:0 0 15px;padding:0;list-style:none}nav ul li{display:block;margin:0;padding:7px 0 8px 25px;color:#666;position:relative}nav ul li a{color:inherit;font-size:16px}nav ul li.active{background-color:#1E88E5;color:#fff}nav ul li.done{color:#aaa;text-decoration:line-through}.btn,.btn:hover{text-decoration:none}nav ul li.active:after{left:100%;top:50%;border:solid transparent;content:" ";height:0;width:0;position:absolute;pointer-events:none;border-color:rgba(21,101,192,0);border-left-color:#1E88E5;border-width:17px;margin-top:-17px}h1,p{margin:0 0 20px}h1{font-size:21px;font-weight:400}.terms{border:1px solid #ddd;background-color:#eee;margin:0 0 15px;padding:25px;border-radius:3px;max-height:200px;overflow-y:auto;font-size:14px}.terms strong{display:block;margin:0 0 4px;font-size:15px}.terms p{margin:0 0 15px}.terms p:last-child{margin-bottom:0}.btn{display:inline-block;padding:0 15px;height:32px;border:1px solid #cfcfcf;background-color:#f7f7f7;line-height:32px;font-family:inherit;font-size:13px;font-weight:500;color:#666;text-align:center;border-radius:2px;-webkit-border-radius:2px;-moz-border-radius:2px;cursor:pointer}.btn:hover{background-color:#f0f0f0}.btn.blue{background-color:#2196F3;border-color:#1E88E5;color:#fff}.btn.blue:hover{background-color:#1E88E5}input.fancy,select.fancy{border:1px solid #ddd;font:inherit;font-size:15px;color:#555;padding:6px 13px;margin:0;width:100%;display:block;box-sizing:border-box;-webkit-box-sizing:border-box;border-radius:2px;-webkit-border-radius:2px;-moz-border-radius:2px}.error,form>table strong{font-weight:500}form>table{margin:40px 0 0}form>table tr td{border-bottom:15px solid #fff}form>table tr td:nth-child(1){padding-right:35px}.error{color:#F44336;font-size:14px;border:1px solid #F44336;border-radius:3px;padding:10px 15px}
        .termsframe { width: 100%; border: 1px solid #ddd; min-height: 300px; }
    </style>
</head>
<body>
    <div class="panel">
        <table cellspacing="0" cellpadding="0">
            <tr valign="top">
                <td width="225px">
                    <div class="logo">
                        <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAVQAAABQCAYAAABGdI0XAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MEFEQjZCOEYzNEY5MTFFNkExQzREQTFGQjlFQzI4MTMiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MEFEQjZCOTAzNEY5MTFFNkExQzREQTFGQjlFQzI4MTMiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDowQURCNkI4RDM0RjkxMUU2QTFDNERBMUZCOUVDMjgxMyIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDowQURCNkI4RTM0RjkxMUU2QTFDNERBMUZCOUVDMjgxMyIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PhW7va0AABWMSURBVHja7F0LkF1Fte0ZQpCvGUClEAhOVESCn7qJQVQkvAygL0KJzPgBhFhyR6t8vOCHGdEUBWg5A08L0RJz5Ykf0PfmihoVRTJBESV8chUxSLSYS0hQMQmZmAQSMGRce+4+sdO3T59f3+/sVbWrZ849p/v0Pvuss/u3u0N5wvWr89ORHPXYtoeOnFATh+HvF0MOY5kBOQRysCYHQvaFHACha/fndC9s3/CsrbhdkKc53Q55HrKN/96myRbIU5BNLBt2bnl+48TuibW3LFr9rBIIBAJPmJiYUB0JSZNI8ETIqyDHQ46FzIQcAzkC0lHe9nuvNxlCqKmxY/OubUzof4es0+QRlodAtlvFPAQCgVdCBYG+EMmZkPmQtzCRdrquaSFCDdUL5M+QeyErID8HwW4QcxEIBKkIFUQ6D8knIO+wNcPbnFBN7IbcAbkWxHqnmI1AIAgj1E4LmQ4gWQl5V1IybVN0spe+4rybZn9R1CEQCMLQYfFM782SYRt6qCbOgaf6AzEdgUAQ5aF+UtQSictEBQKBIKw5G3inhyJ5u6gkEieh6f8KUYNAIAglVOAMVZkXKojG2aICgUDgItTTRB2xIboSCAROQp0r6oiNk9Hs7xQ1CASCKkLlZaMniDpigxY8vFzUIBAIbB7qbMi0KVDfMmS9qqyGyorXiPkIBAIbob62zet5D3ngN5794KxbFq0mT5xiD/y/EKpAIKgFoR7XxnX8A6QHZPrH4ABI9Qkk74V8L0O+rxTzEQgENkI9to3reCXI9BnzIEiVmv1ZFjLIXFSBQDDlCPW+sB9Aqo8ieQOkqCoxVZOgW8xHIBBMNUI91PUjSPUBSB97nF9WlcDVcTDjvJtmv0BMSCAQBJh2/eo8Rcp/SZvWj8LtrQn78eirHz9o/ZKZ25lYH0PyXyDJK5B+GPIRVQma7cKRqjJzQCAQJMDcK+4yZ9r0PHDlW0dbnlAhL23TZ/YQ5Jwbz37wuRAyPRrJPUiJEK+F3AZynQCxbsbfnwWx0rELIB+FvDqkjCOEUAWCupFwL5IhVeluK0AGQcLjzUaoh7eh7mmu6dtBpv9g8qSgLwsgP4bshJwC+RjkRZCj+P81OO/zSL8NYn0WxEpE/L8g1q+rStAYOn++Uc5RYuYCQV3IlEh0RDuU57RfCLW2oI35zgSZ/oXJlJbU0vQo6tq41HEdbe/yNchncM2XkN4AYt3MswFuIwG55phYqc91H1XZiFAgENQevZZj+WYj1E4V3U/YSiDv8x3BnFMQIw00/ZTJNC6oP/kzkHVErJCXBT+AXEuQ9+HPWZDrVGUJqkAgaAzGm+2GyEM9rE2US3s/nQ8y/TWTKXmPt2fwwGmHVxqY+jDy+j7Sa+GxPsDE+niEtyuYms3SthxoaRJQnyltz9SlHRtutpvsbKMm/2KQ6a1MpgeyZ+pjrug+3Ny4H/neBVkI6RD7FgjqBx58msPESh+pfhxrOkIlD3VGG+j7GpDpl5hMqU7UZ5qrQTmnKMsAlpi7QFAXUqUZNf3NfI/koR7U4nq+BTLIZEqeIw0snVnjMoMBrBVi5gKBoF0IlVz/D8A7DfquroJcVMfyDxcTEggEepP/YE95kbe2okN17DuhJk5V1XM2fYP2qz43mLgP75SaAp+us/5k6alAINiLULNO/ZkcXb9kduG72rGrFq+c18vN8Vps/LdO7T1xnzbN+0oD9DdDTEggEOiEmrXJ/wWDTCdx3RvvK4JUT8SfSzzfMy0NPQNk+lcm05ORUPmN2ONp/1pm/r5v/gdNEaEPU04THdTlQR31Jfr7OxeuKGcoyyxHn55SZqHySihn1HM9qWyakbGA0+6Qsssou5i1vLlX3JVIrzwYYstnIKKoXpwTNThaNPOvxfQr5Em6Xa4fQ54dKfPq1vTXbehvnPUW6M6brfjWCy9ljWV3KCeW3U3z4EEujfjt4x6JZwfkLJDpGiZTCoz9o1oTmwPTcQ+d65fM3F0DMqWX1Zx3Z2KBcQ09/OG4hMeEnY9RTrdmeHRdmcspZPxY5Fm6E5ZN5RZQ/niKlyixXnHNpF4tL+9QRHH5GLdUUi0SD4KJdCCiXl2sP5IBXFNm3RWapA6p7I7rMWl3rvgB5NUd4KH5bQW8VFr+SQsHqEl+E2Rjgnypb/ReyP2qsgIqmLj/GyZTWuF1u2r8woRDfHulkOX8snYlvHzSC+Hr43iFYynLIUNbSuUwMabxSIOyu1OUTdeNcT6xXyRIJr3y9VMS/CEai/mRqLIV0h2TWSPr4MXuOJ9QD3V6yvvbpSqj6rtcJ4FUd7AX+aPFK+cRgb+RCZYkbBsRWpnUj2s30T8fXPY66uedCzIdZTIlEqOJ+8e2oe2OmB6S0QQJ9aSMc6O8X5d3NRrytbYRDRHbnLjdDTHKLqm9lxR2KfucYjo+gvwGUfZwM+i1TYm0i1uaro9XHB0uYDKiZnqpQR8Eb3aH/AZtCwvS7nT6MOT9l8wu/NZ1EojvDCS/R5P4SSZX8jJ/w3IZCPY4jVyJaKlP525IH87dE0GfB58CMqUPAE3cf32T2Jy33WLZ4zINkfpuBsMIC9cEzau85nkNO8oY4mabjUQLYX2UWn/ugEGuAbH1RDXBcc7SEA+nzPdctOXhKHuy2U2/47rBCM/EqldH/2ikXs0+yDZdehpGppFNYNZ7XtN9F3urPXUm00i7s9VB62u32h39jusGTTJIMm2KDOYLkE+DTHcywVEf7FmQZSDOXXyMPNHLIVfTNfj/fvZS6ZyHNe/1T0iuIQG50tr7hZAHdTK14EYy1CYyOBrU2+QpL9Nwh11EQeD+UpJB9v66HOTbayHTydUnUf2uTHSTL5DFy8xxvoMOMs1bjHqc6zicsGyzD3SA+lYdfbpVejVfBBNMhJN6Ze+mK4x827yZbyPTwTjLPnkgp8jEulTz+obqWIdQu4uqA5PspN2F9L1P9hHr/cMd16/Ox92jfi17pXdrHujxSL7NSnqCDZC+2jQH9ZiQfKgPg+KSLiNvFASbaC8nlLlV+Zs76wMvQx3WevJQNxsP7NA0Ay8OD3PMyJ+aOT1pymByHjEOz7KReUjZ41x2KUXZZG/LLfnNstUFRl+l11oEJvbloTbDKD8PQI1ZfuqLO+Jt5Jdjewnru4ysY1K9sIdptbs03Q5cB6vdkT1NTEzEnmpEXuGJAZmSBwqhaEu/0/oZKNjyRZALHWQ6+dJBFkN+AdmIfL4FORcSSZI4h0bzD1Dtiy6LZ+YLAyGElqoM7hoYtJQRp2zFXnEpZdl0Xb9FdwNx9NpsUd6bFDZd9qchU9Y5PbM+Vd+Qe1a7S9uHy9c57S6KUKnvcyGI9GLIdia1maqyKoqa/vt5IJALuD9rE/L+GeSdjvPPUZXoT82E/WqVMfeP+vJOzWbPYFbC5qZ62dG0Diu7kHU+KV9vNvHzcWYdsLcmcHt25jMrZp36xIQ03MA6FNJ+EIxujCq7C2YwuAiV3PPZINLbNA9xEZI/QE6tgQ5osImCmoygnLdZvFPqXriuCe1vmse8quY5cvM2K3qNL3U5yxxS00j1D6TlI9Br8RJ8vVTDlg90bxy9xphwP5Vh7Tf1kTH3W5brVIe6252NDKi/6SP66icO1kzRlc6qE0H9BGXSslWaGkXbOp/GX5tmbO4/7TEv+vrphDTZZwOSCkbA0xpizlJOrT4COeOYWXYpy4ouw0stQzclo4zuuHoFqQYjvLLRotteSp51VHR0zzR9HSgf2I7V7kwPlSbKv8YgU2qCP1wnMtW7IqgrgO6DZgcsVu3ddxoQRMFCUPT1Cyay0/QkGtFOMyl5L+PyeM8ly/26yi56Vlsx4kVS3FQN1SteDppXOMADMYLaP7PRNqiD1e6maV4WbT5XAJlOMJHSZPrrIe8X+6orqON+ubJPKu5lGWLPbJQ91yiCNPMiYq6XZ5CrFZkH3RdRhJpEr+x5jLLnWpqiNpiL0HFWlBpQh7rYHREq7cF0EYh0TPNKqYn9DfqzRQ1iG+RXkFWqMm1iGx+nbaNpdRYtIjhJNd8AVzCyPyfGWv4gqMcAk2vB0S/ayCV/tS57PE55wRYaMdby79Erk2uhWdahN/CZeR2Zp2cB3bal3RGhvhVkupuJlJrVn4Nc0qKGQJvo0eyDH65fMnOn60TuFybvm6Z/HdmExDoMoqQXOa/+HdnH9TVeypPnU09HmgqgQRG8zIn0ypPD+6ewxyqIiWkamb4Bybcgx7VgPWh613+DREfiXoBzNyD5H9T7y+yxfEqli7y1u1aVYm+VBk6Gud80WEK5wEEAy3kZaMn4mupf7IKq3UjruOX/rhp6Dom9KfZWJ/WqhaKL1Guj1qE3wGMbr6WHV6cgKQ2xu2m8dJRill7ejE3gGPgl5N1MkInBnuyVNAdWVYKyvDRhFlvr5LGWNXINpmnY1qh3Manqq4ZKxnnlmAFFfMAs2/fAT6YBNx75Dcg1Uq84Z1aLLgzozvDMzJkbWZFrV7vrZEJa0qJkSstXz0hLpgaxUrwB6lcdS3jpcw3oDhin/lIIxTTosZCIOam51ACDDiu713P+vVkI1fRcqb8UElevzehdxtVTI5/Zgna1OyLUo1q0CUODTn0gwipCO/+O8RdAzoP8H+QRyBbIJsjvIDdA5oeQKsUjOF0liNuKa55ppBI4qEmPcodPqzKuFFOvfBl2zlfZnE9NRnN5jXiUXmvZXPX2AeRFDEnyqBrB9jylrLcRduerDpxPLoxQW3Ff+Y3czLeR6flIHoPcTOeoypbPNAWMAlG/DvIhyJ047wFIzkKQZEznx7yPnc2gDK2v1fri8zJN8yUdqtO92cr2NanbzGfcxxYpusfq0msLeVdJn3XNnhkP8NX8Y85LROtmd8GSViLUZ1Tr4eNBjFWNSPeB0Gouin51RIw85kBW4ppFFlK9g/OJQjN9jKIGmYYtXmpmTyFmxH6z7HzWsvn6fEQ59dBrPQi1O8b+VWEENpD0I8AfEvPDlGcyzOopD9VRj8OWOvRmrIPT7ohQt7UYmT4SQnY3QD6YMC8akPs6SLXP8httSf3PiOu3+6oUNV/TbikSvHQRL2XB8sVemiVWAF+7iqdrueC1bL5uqaWZXLA1zzJuv5Fk0MtX94CtjIGk8QeYAIdCuhHikFFVqyZtYBluJgcxUesFq92ljePA1zntrrNZmq0J8Hl4kBOGd0qEeHGGPIlUZxpeKu2V9b16ESo3I4ItRfIJCcYWuq5k6RawhR5bxYsIkpIaXbNc/Xt/qQURXRK2spcnLVsr1xYOcNyl16QeFpPwQAJCrWqqu4g8jJy4+Vi26SuOp8ofkRHj5U/kvWuzH1Ldg6WetMgmpxFdzcGedr+nOoTanT7ro+Poqx8fUfXpJPYB8hgPB9lt1ciUolQ9qrKv6rr55tO7LtAPQDe0g8CPHdfch3s5yYd3qqpnFwRbTDiDojCRDanqTvKwYM9he+s4tyHRPMNgWxDTcxvlWQdRZJi47IgtUAIyLYR4RaF6dQXLYBKw6jViW+khC8kOB31s7OUEOqSXfpajaTni8ISLqnofpGDwqdfiqRVVim2k024fYjSPzQFSspPNxuneA0xHPJfIOkRsgRKQ6R67owDTRKiFjN5dPUER/k8xvFOKkXqrh7xp+5YjQKpPaYR6IJItKjxE3+24n7d5ItQRFT4Sq+8Rbr48tgft3LguxkZ5tk3Xco7mWtHhISYtO+5maZF1ZUL1qlfXthkhEeKjMCdssYCDzJJ2H/RwnRITasz7iLvR4Z5o+WnIMctOBh436Qu1hSBi/5YWau7bNgVc6ClvIs0zjWY/BY1Z47jGi+7Ik4TQINmgox8v8JgC6Q156ePs0US/u6KnB+Xp0hXyghCh9cUNVs1l26Yj6YSml+siwx5XXcmThHjTa8w9iJLGDc078uvP2DwuMulkWojA9+Gql81eTIyyd9+QlWb87LzZXZgttBqhPmY59lqP+Z9oOba21oRqkM0sZR8QiMIoE8xgzLKKXNZgirIC4piVZsUVz50NiC7pKHqZr5sTtbGg8TJl0mvUpn5aWQXutxuPqcPBGGTWl1BPdC7t/dTna1WXpsNCiudFTeOeRq8wY282s925vGJq8tO8zBtahFAXwWv8htHkJ8Kb6Sn/m9Dk/4B+gPa8UpXYrDZ8Dvdzea0qy1ODujUPMWe86OPcVClmDdrMZeU06bI06aisks+5npZ6dhseot4sL/som/v2Yuk1bVBibQsOvU6jmh6LSQlG2w672/ACg/sN8i1Zrk20SV+Mupn2YruXUtiWI/Vu8sewA6fdxdk6JehDpQDS328RQr0YBHajQaiP8pfTB5aCUD9kECop8tyQ8y/F/VynBALBlEfQh/pkC93zixM2yZPiccuxFznO/5uYkUAgCECEuqGF7tcWWnClx/zvsRw7wXH+X8SEBAKBTqit5GW92XLsu57yJnL8tdHcJwI/3HHNk2JCAoFgD6FytKSnWuR+u0Fye3mMN5/e9UckP/SQ9xDyet449s4YJCwQCAR7PFTC2ha6Z9t6fdoVNcsUpnshXzW8U9LNIsc1m/Ex2iEmJBAIWplQL+b9oHQvlQaT3gVJQ3C0suUc5LHLOE5z/17puO7PYj4CgaDVCZWWg1ZNJgch3omE9kZelyAv2nrxTbjW1o+8KqJJL4QqEAishLq6xe77Ip4/a5IqjfjPhlyp3LMXHoZcCJmPa/5uOwHNeZrfeqqDVIVQBQLBXphcKQFymovk/ha7dwqdNx/Et8r2IwWcRjIP8npVmUtKA07rIStBon+KWwh083JV2XfL3LzvXJR9q5iQQCAgTK6UYtI4gAmqo8XqQMvcFoLY7qllISGkejzKXSNmJBAI9iJUJg1q9p/QgvWgbUguhXzVDDxdQ1KleKwzalmeQCBoPULt1P6/u0XrsR/kK3T/IL1EwZ5x/r4Q6o+dH3Wu0ae6UshUIBCY0D3U9yh/q44aCWr+046nd4D0xmwkqiq7n9I0q/NUZRvtHdx1cGdMT3Uezr1FzEcgEIQ1+Q9FQiPe09qojjTZnzzL7VwvCjv2Csh0y7mxSVUgEAichMqkugzJWVNYJ0KqAoEgNaF2GseumeI62R/yE3xYThPzEAgESVE1TQpksgTJVVNcL+KpCgSCbE1+jVRPRnIZ5D9Ve/WpCqkKBIL6EqpGrDRQdTqEtm5+k6os6+xsd71AKCQgTSOjbaKXiakIBILMhGoh2IOQHM/yalWJxnQ05Bhl356kmUFr/dexjDGJ0hr/R0Ci28U8BAJBTQk1gmz3Y2KldfMU5f4lkMP47xdCDoYcwikJLXedbqT7xiyOQu09DXkOQgGyn+UmOhHhVpZ/cLoZsklVpoRtZHkCpLlTTEAgEPgk1H8JMACctE20AEjWcAAAAABJRU5ErkJggg==" width="150px">
                    </div>

                    <nav>
                        <ul>
                            <li class="done"><a>License agreement</a></li>
                            <li class="done"><a>File extraction</a></li>
                            <li class="done"><a>Database setup</a></li>
                            <li class="done"><a>Activate license</a></li>
                            <li class="done"><a>Google network</a></li>
                            <li class="done"><a>Setup account</a></li>
                            <li class="done"><a>Install plugins</a></li>
                            <li class="active"><a>Updates</a></li>
                            <li><a>Configure cron</a></li>
                        </ul>
                    </nav>
                </td>
                <td>
                    <h1>Updates</h1>
                    <?php if ($error != "") echo "<div style=\"margin: 10px 0;\" class=\"error\">$error</div>"; ?>

                    <p><?php echo number_format($rows); ?> updates are available. Please configure update settings. <?php if ($rows > 0) echo "When setup is complete, you will have the option to install the update(s)."; ?></p>

                    <form action="" method="post">
                        <div style="padding: 0 0 6px;"><label for="cb1"><input id="cb1" type="checkbox" name="automatic-updates" value="On" checked> Automatically update SEO Studio</label></div>
                        <div style="padding: 0 0 6px;"><label for="cb2"><input id="cb2" type="checkbox" name="automatic-updates-purchased" value="On" checked> Automatically update plugins and themes</label></div>
                        <div style="padding: 0 0 6px;"><label for="cb3"><input id="cb3" type="checkbox" name="automatic-updates-backup" value="On" checked> Create backups of affected files before updating</label></div>

                        <br>

                        <p>Also some irrelevant settings...</p>

                        <div style="padding: 0 0 6px;"><label for="cb4"><input id="cb4" type="checkbox" name="send-errors" value="On" checked> Send errors to the developer</label></div>
                        <div style="padding: 0 0 6px;"><label for="cb5"><input id="cb5" type="checkbox" name="send-usage-info" value="On" checked> Send anonymous usage statistics to help support development</label></div>

                        <br>

                        <input type="submit" class="btn blue" value="Save & continue" name="go">
                    </form>
                </td>
            </tr>
        </table>
    </div>

    <script>!function(e,o,n){window.HSCW=o,window.HS=n,n.beacon=n.beacon||{};var t=n.beacon;t.userConfig={icon:'message'},t.readyQueue=[],t.config=function(e){this.userConfig=e},t.ready=function(e){this.readyQueue.push(e)},o.config={docs:{enabled:!1,baseUrl:""},contact:{enabled:!0,formId:"6c2e0c2c-44df-11e6-aae8-0a7d6919297d"}};var r=e.getElementsByTagName("script")[0],c=e.createElement("script");c.type="text/javascript",c.async=!0,c.src="https://djtflbt20bdde.cloudfront.net/",r.parentNode.insertBefore(c,r)}(document,window.HSCW||{},window.HS||{});</script>
</body>
</html>
