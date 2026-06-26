$ErrorActionPreference = "Stop"

function Invoke-Json {
    param(
        [Parameter(Mandatory)][string]$Uri,
        [ValidateSet('GET','POST')][string]$Method = 'GET',
        [hashtable]$Headers,
        [hashtable]$Body
    )
    try {
        $params = @{ Uri = $Uri; Method = $Method }
        if ($Headers) { $params.Headers = $Headers }
        if ($Body) {
            $params.ContentType = 'application/json'
            $params.Body = ($Body | ConvertTo-Json -Compress)
        }
        return Invoke-RestMethod @params
    } catch {
        if ($_.Exception.Response) {
            $sr = New-Object IO.StreamReader $_.Exception.Response.GetResponseStream()
            $raw = $sr.ReadToEnd()
            Write-Output $raw
        } else {
            throw
        }
    }
}

$ts = Get-Date -Format yyyyMMddHHmmss
$username = "e2f_user_$ts"
$email = "e2f_$ts@example.com"
$password = "S1mple@Pass1!"

Write-Host "=== Register ($username) ==="
$reg = Invoke-Json -Uri "http://localhost/1st_project/API/v1/profile/user_registration.php" -Method POST -Body @{
    full_name = "E2F $ts"
    username = $username
    email_address = $email
    phone_no = "0300123456"
    gender = "male"
    date_of_birth = "1995-01-15"
    password = $password
}
$reg | ConvertTo-Json -Depth 6

Write-Host "=== Login ==="
$login = Invoke-Json -Uri "http://localhost/1st_project/API/v1/auth/login.php" -Method POST -Body @{
    username = $username
    password = $password
}
$login | ConvertTo-Json -Depth 6

$token = $login.DATA.token
$refresh = $login.DATA.refresh_token
if (-not $token) { throw 'No token in login response' }

Write-Host "=== Validate (header) ==="
$val = Invoke-Json -Uri "http://localhost/1st_project/API/v1/auth/token_validation.php" -Method GET -Headers @{ Authorization = "Bearer $token" }
$val | ConvertTo-Json -Depth 6

Write-Host "=== Profile (GET) ==="
$prof = Invoke-Json -Uri "http://localhost/1st_project/API/v1/profile/profile.php" -Method GET -Headers @{ Authorization = "Bearer $token" }
$prof | ConvertTo-Json -Depth 6

Write-Host "=== Update Profile ==="
$upd = Invoke-Json -Uri "http://localhost/1st_project/API/v1/profile/update.php" -Method POST -Headers @{ Authorization = "Bearer $token" } -Body @{
    full_name = "E2F Updated $ts"
}
$upd | ConvertTo-Json -Depth 6

Write-Host "=== Refresh Token ==="
$ref = Invoke-Json -Uri "http://localhost/1st_project/API/v1/auth/refresh-token.php" -Method POST -Body @{
    refresh_token = $refresh
}
$ref | ConvertTo-Json -Depth 6

Write-Host "=== Logout ==="
$logout = Invoke-Json -Uri "http://localhost/1st_project/API/v1/auth/logout.php" -Method POST -Headers @{ Authorization = "Bearer $token" }
$logout | ConvertTo-Json -Depth 6

Write-Host "=== DONE ==="

