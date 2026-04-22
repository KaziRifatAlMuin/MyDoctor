$allowedHex = @(
'#0B3C5D','#154C79','#1E5FAF','#2A7DE1','#3A8DFF','#5AA9E6','#7FB3FF',
'#EAF3FF','#DCEBFF','#CFE2FF','#B6D4FE','#F4F8FF',
'#FFFFFF','#FAFBFC','#F8F9FA','#F1F3F5','#EEF2F7',
'#0F172A','#1A1A1A','#2C3E50','#334155','#6B7280','#9CA3AF',
'#16A34A','#22C55E','#4ADE80','#DCFCE7',
'#F59E0B','#FBBF24','#FEF3C7',
'#DC2626','#EF4444','#FCA5A5','#FEE2E2',
'#0D9488','#14B8A6','#5EEAD4','#06B6D4','#67E8F9'
)

function HexToRgb([string]$hex6){
  [PSCustomObject]@{
    R = [Convert]::ToInt32($hex6.Substring(0,2),16)
    G = [Convert]::ToInt32($hex6.Substring(2,2),16)
    B = [Convert]::ToInt32($hex6.Substring(4,2),16)
  }
}

$allowed = $allowedHex | ForEach-Object {
  $hex = $_.ToUpper().TrimStart('#')
  $rgb = HexToRgb $hex
  [PSCustomObject]@{ Hex = ('#' + $hex); R = $rgb.R; G = $rgb.G; B = $rgb.B }
}

function NearestHex([int]$r,[int]$g,[int]$b){
  $best = $null
  $bestDist = [double]::MaxValue
  foreach($c in $script:allowed){
    $dr = $r - $c.R; $dg = $g - $c.G; $db = $b - $c.B
    $dist = ($dr*$dr)+($dg*$dg)+($db*$db)
    if($dist -lt $bestDist){ $bestDist = $dist; $best = $c.Hex }
  }
  return $best.ToLower()
}

$regex68 = [regex]'#[0-9A-Fa-f]{6}([0-9A-Fa-f]{2})?\b'
$files = Get-ChildItem -Path resources/views -Recurse -Filter *.blade.php
$updated = 0
foreach($f in $files){
  $text = Get-Content -Raw -Path $f.FullName
  $orig = $text
  $text = $regex68.Replace($text, {
    param($m)
    $tok = $m.Value
    $h = $tok.TrimStart('#')
    if($h.Length -eq 6){
      $rgb = HexToRgb $h
      return NearestHex $rgb.R $rgb.G $rgb.B
    }
    if($h.Length -eq 8){
      $rgb = HexToRgb $h.Substring(0,6)
      $a = [Convert]::ToInt32($h.Substring(6,2),16) / 255.0
      $nearest = NearestHex $rgb.R $rgb.G $rgb.B
      $nrgb = HexToRgb $nearest.TrimStart('#')
      return ('rgba({0}, {1}, {2}, {3})' -f $nrgb.R, $nrgb.G, $nrgb.B, [Math]::Round($a,3))
    }
    return $tok
  })

  if($text -ne $orig){
    Set-Content -Path $f.FullName -Value $text -NoNewline
    $updated++
  }
}
Write-Output "Updated files: $updated"