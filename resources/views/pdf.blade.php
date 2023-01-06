<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="{{ public_path('../resources/css/app.css') }}" type="text/css">
    <title>Sales report</title>
  </head>
  <body>
    <table class="table table-bordered">
    <thead>
      <tr>
        <td colspan="8" class="align-text"><b>Gama Baja</b></td>
        <td colspan="8" class="align-text"><b>Gama Media</b></td>
        <td colspan="8" class="align-text"><b>Gama Alta</b></td> 
        <td colspan="8" class="align-text"><b>Total</b></td>    
      </tr>
      </thead>
      <tbody>
      <tr>
        <td colspan="4" class="align-number">Total vendido</td>
        <td colspan="4" class="align-number">Utilidad</td>
        <td colspan="4" class="align-number">Total vendido</td>
        <td colspan="4" class="align-number">Utilidad</td>
        <td colspan="4" class="align-number">Total vendido</td>
        <td colspan="4" class="align-number">Utilidad</td>
        <td colspan="4" class="align-number">Total vendido</td>
        <td colspan="4" class="align-number">Utilidad</td>              
      </tr>
      <tr>
        <td colspan="4" class="align-number">{{$reportData->lowrange_total}}</td>
        <td colspan="4" class="align-number">{{$reportData->lowrange_profit}}</td>
        <td colspan="4" class="align-number">{{$reportData->midrange_total}}</td>
        <td colspan="4" class="align-number">{{$reportData->midrange_profit}}</td>
        <td colspan="4" class="align-number">{{$reportData->highrange_total}}</td>
        <td colspan="4" class="align-number">{{$reportData->highrange_profit}}</td>
        <td colspan="4" class="align-number">{{$reportData->total}}</td>
        <td colspan="4" class="align-number">{{$reportData->profit}}</td>              
      </tr>      
      </tbody>
    </table>
  </body>
</html>