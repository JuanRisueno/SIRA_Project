<style>
body {
    font-family: 'Inter', 'Helvetica', 'Arial', sans-serif;
    color: #000000;
    background-color: #ffffff;
    margin: 0;
    padding: 10px;
}
.tarjetas-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin-top: 15px;
}
.tarjeta {
    border: 2px dashed #000000;
    border-radius: 8px;
    padding: 12px;
    background-color: #ffffff;
    page-break-inside: avoid;
    box-sizing: border-box;
}
.tarjeta-header {
    font-size: 0.95em;
    font-weight: bold;
    text-align: center;
    border-bottom: 1.5px solid #000000;
    padding-bottom: 5px;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.tarjeta-content {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.tarjeta-left {
    flex: 1.3;
}
.tarjeta-right {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 85px;
    text-align: center;
    border-left: 1px dotted #000000;
    padding-left: 10px;
}
.credenciales-tabla {
    width: 100%;
    border-collapse: collapse;
}
.credenciales-tabla th, .credenciales-tabla td {
    padding: 4px 5px;
    text-align: left;
    border: 1px solid #000000 !important;
    font-size: 0.7em;
}
.credenciales-tabla th {
    background-color: #f2f2f2;
    font-weight: bold;
}
.empresa-col {
    white-space: nowrap;
}
.valor {
    font-family: 'Courier New', Courier, monospace;
    font-weight: bold;
    white-space: nowrap;
}
.qr-image {
    width: 70px;
    height: 70px;
    display: block;
    margin-bottom: 4px;
}
.qr-label {
    font-size: 0.65em;
    font-weight: bold;
    font-family: 'Courier New', Courier, monospace;
    word-break: break-all;
}
.footer-tarjeta {
    margin-top: 10px;
    font-size: 0.7em;
    text-align: center;
    border-top: 1px dotted #000000;
    padding-top: 5px;
    font-style: italic;
}
</style>

<div class="tarjetas-grid">

  <!-- Tarjeta 1 -->
  <div class="tarjeta">
    <div class="tarjeta-header">SIRA — CLIENTES DE PRUEBA</div>
    <div class="tarjeta-content">
      <div class="tarjeta-left">
        <table class="credenciales-tabla">
          <thead>
            <tr>
              <th>Cliente</th>
              <th>CIF (Usuario)</th>
              <th>Contraseña</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="empresa-col">Sol de Almería</td>
              <td class="valor">B04XXXXXX</td>
              <td class="valor">sol1234</td>
            </tr>
            <tr>
              <td class="empresa-col">David Martín</td>
              <td class="valor">A12345678</td>
              <td class="valor">sol1234</td>
            </tr>
            <tr>
              <td class="empresa-col">Sergio Pérez</td>
              <td class="valor">B87654321</td>
              <td class="valor">sol1234</td>
            </tr>
            <tr>
              <td class="empresa-col">Ana López</td>
              <td class="valor">C11222333</td>
              <td class="valor">sol1234</td>
            </tr>
            <tr>
              <td class="empresa-col">Laura García</td>
              <td class="valor">D44333444</td>
              <td class="valor">sol1234</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="tarjeta-right">
        <img src="QR.png" class="qr-image" alt="QR Acceso" />
        <div class="qr-label">52.5.206.103</div>
      </div>
    </div>
    <div class="footer-tarjeta">Defensa TFG ASIR — Mayo 2026</div>
  </div>

  <!-- Tarjeta 2 -->
  <div class="tarjeta">
    <div class="tarjeta-header">SIRA — CLIENTES DE PRUEBA</div>
    <div class="tarjeta-content">
      <div class="tarjeta-left">
        <table class="credenciales-tabla">
          <thead>
            <tr>
              <th>Cliente</th>
              <th>CIF (Usuario)</th>
              <th>Contraseña</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="empresa-col">Sol de Almería</td>
              <td class="valor">B04XXXXXX</td>
              <td class="valor">sol1234</td>
            </tr>
            <tr>
              <td class="empresa-col">David Martín</td>
              <td class="valor">A12345678</td>
              <td class="valor">sol1234</td>
            </tr>
            <tr>
              <td class="empresa-col">Sergio Pérez</td>
              <td class="valor">B87654321</td>
              <td class="valor">sol1234</td>
            </tr>
            <tr>
              <td class="empresa-col">Ana López</td>
              <td class="valor">C11222333</td>
              <td class="valor">sol1234</td>
            </tr>
            <tr>
              <td class="empresa-col">Laura García</td>
              <td class="valor">D44333444</td>
              <td class="valor">sol1234</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="tarjeta-right">
        <img src="QR.png" class="qr-image" alt="QR Acceso" />
        <div class="qr-label">52.5.206.103</div>
      </div>
    </div>
    <div class="footer-tarjeta">Defensa TFG ASIR — Mayo 2026</div>
  </div>

  <!-- Tarjeta 3 -->
  <div class="tarjeta">
    <div class="tarjeta-header">SIRA — CLIENTES DE PRUEBA</div>
    <div class="tarjeta-content">
      <div class="tarjeta-left">
        <table class="credenciales-tabla">
          <thead>
            <tr>
              <th>Cliente</th>
              <th>CIF (Usuario)</th>
              <th>Contraseña</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="empresa-col">Sol de Almería</td>
              <td class="valor">B04XXXXXX</td>
              <td class="valor">sol1234</td>
            </tr>
            <tr>
              <td class="empresa-col">David Martín</td>
              <td class="valor">A12345678</td>
              <td class="valor">sol1234</td>
            </tr>
            <tr>
              <td class="empresa-col">Sergio Pérez</td>
              <td class="valor">B87654321</td>
              <td class="valor">sol1234</td>
            </tr>
            <tr>
              <td class="empresa-col">Ana López</td>
              <td class="valor">C11222333</td>
              <td class="valor">sol1234</td>
            </tr>
            <tr>
              <td class="empresa-col">Laura García</td>
              <td class="valor">D44333444</td>
              <td class="valor">sol1234</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="tarjeta-right">
        <img src="QR.png" class="qr-image" alt="QR Acceso" />
        <div class="qr-label">52.5.206.103</div>
      </div>
    </div>
    <div class="footer-tarjeta">Defensa TFG ASIR — Mayo 2026</div>
  </div>

  <!-- Tarjeta 4 -->
  <div class="tarjeta">
    <div class="tarjeta-header">SIRA — CLIENTES DE PRUEBA</div>
    <div class="tarjeta-content">
      <div class="tarjeta-left">
        <table class="credenciales-tabla">
          <thead>
            <tr>
              <th>Cliente</th>
              <th>CIF (Usuario)</th>
              <th>Contraseña</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="empresa-col">Sol de Almería</td>
              <td class="valor">B04XXXXXX</td>
              <td class="valor">sol1234</td>
            </tr>
            <tr>
              <td class="empresa-col">David Martín</td>
              <td class="valor">A12345678</td>
              <td class="valor">sol1234</td>
            </tr>
            <tr>
              <td class="empresa-col">Sergio Pérez</td>
              <td class="valor">B87654321</td>
              <td class="valor">sol1234</td>
            </tr>
            <tr>
              <td class="empresa-col">Ana López</td>
              <td class="valor">C11222333</td>
              <td class="valor">sol1234</td>
            </tr>
            <tr>
              <td class="empresa-col">Laura García</td>
              <td class="valor">D44333444</td>
              <td class="valor">sol1234</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="tarjeta-right">
        <img src="QR.png" class="qr-image" alt="QR Acceso" />
        <div class="qr-label">52.5.206.103</div>
      </div>
    </div>
    <div class="footer-tarjeta">Defensa TFG ASIR — Mayo 2026</div>
  </div>

  <!-- Tarjeta 5 -->
  <div class="tarjeta">
    <div class="tarjeta-header">SIRA — CLIENTES DE PRUEBA</div>
    <div class="tarjeta-content">
      <div class="tarjeta-left">
        <table class="credenciales-tabla">
          <thead>
            <tr>
              <th>Cliente</th>
              <th>CIF (Usuario)</th>
              <th>Contraseña</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="empresa-col">Sol de Almería</td>
              <td class="valor">B04XXXXXX</td>
              <td class="valor">sol1234</td>
            </tr>
            <tr>
              <td class="empresa-col">David Martín</td>
              <td class="valor">A12345678</td>
              <td class="valor">sol1234</td>
            </tr>
            <tr>
              <td class="empresa-col">Sergio Pérez</td>
              <td class="valor">B87654321</td>
              <td class="valor">sol1234</td>
            </tr>
            <tr>
              <td class="empresa-col">Ana López</td>
              <td class="valor">C11222333</td>
              <td class="valor">sol1234</td>
            </tr>
            <tr>
              <td class="empresa-col">Laura García</td>
              <td class="valor">D44333444</td>
              <td class="valor">sol1234</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="tarjeta-right">
        <img src="QR.png" class="qr-image" alt="QR Acceso" />
        <div class="qr-label">52.5.206.103</div>
      </div>
    </div>
    <div class="footer-tarjeta">Defensa TFG ASIR — Mayo 2026</div>
  </div>

  <!-- Tarjeta 6 -->
  <div class="tarjeta">
    <div class="tarjeta-header">SIRA — CLIENTES DE PRUEBA</div>
    <div class="tarjeta-content">
      <div class="tarjeta-left">
        <table class="credenciales-tabla">
          <thead>
            <tr>
              <th>Cliente</th>
              <th>CIF (Usuario)</th>
              <th>Contraseña</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="empresa-col">Sol de Almería</td>
              <td class="valor">B04XXXXXX</td>
              <td class="valor">sol1234</td>
            </tr>
            <tr>
              <td class="empresa-col">David Martín</td>
              <td class="valor">A12345678</td>
              <td class="valor">sol1234</td>
            </tr>
            <tr>
              <td class="empresa-col">Sergio Pérez</td>
              <td class="valor">B87654321</td>
              <td class="valor">sol1234</td>
            </tr>
            <tr>
              <td class="empresa-col">Ana López</td>
              <td class="valor">C11222333</td>
              <td class="valor">sol1234</td>
            </tr>
            <tr>
              <td class="empresa-col">Laura García</td>
              <td class="valor">D44333444</td>
              <td class="valor">sol1234</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="tarjeta-right">
        <img src="QR.png" class="qr-image" alt="QR Acceso" />
        <div class="qr-label">52.5.206.103</div>
      </div>
    </div>
    <div class="footer-tarjeta">Defensa TFG ASIR — Mayo 2026</div>
  </div>

</div>
