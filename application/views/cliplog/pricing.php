<b>Rights Managed Pricing: </b>
<br>
<table <?php if (sizeof($keywords) > 0) { ?>id="example"<?php } ?> class="table table-striped display" cellspacing="0"
       width="100%" data-page-length='40'>
    <thead>
    <tr>
        <th>Category</th>
        <th>Use</th>
        <th>Territory</th>
        <th>Term</th>
        <th>Description</th>
        <th>Exclusions</th>
        <th>Clip Minimum</th>
        <th>Budget</th>
        <th>Standard</th>
        <th>Premium</th>
        <th>Gold</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($rmData as $data) { ?>

        <tr>
            <td><?php echo $data['Category']; ?></td>
            <td><?php echo $data['Use']; ?></td>
            <td><?php echo $data['Territory']; ?></td>
            <td><?php echo $data['Term']; ?></td>
            <td><?php echo $data['Description']; ?></td>
            <td><?php echo $data['Exclusions']; ?></td>
            <td><?php echo $data['Clip Minimum']; ?></td>
            <td><?php echo $data['Budget']; ?></td>
            <td><?php echo $data['Standard']; ?></td>
            <td><?php echo $data['Premium']; ?></td>
            <td><?php echo $data['Gold']; ?></td>
        </tr>

    <?php } ?>

    </tr>

    </tbody>
</table>

<br>
<br>
<br>

<b>Royalty Free Pricing: </b>
<br>
<table <?php if (sizeof($keywords) > 0) { ?>id="example"<?php } ?> class="table table-striped display" cellspacing="0"
       width="100%" data-page-length='40'>
    <thead>
    <tr>
        <th>License</th>
        <th>Terms</th>
        <th>Budget</th>
        <th>Standard</th>
        <th>Premium</th>
        <th>Exclusive</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($rfData as $data) { ?>

        <tr>
            <td><?php echo $data['License']; ?></td>
            <td><?php echo $data['Terms']; ?></td>
            <td><?php echo $data['Budget']; ?></td>
            <td><?php echo $data['Standard']; ?></td>
            <td><?php echo $data['Premium']; ?></td>
            <td><?php echo $data['Exclusive']; ?></td>
        </tr>

    <?php } ?>

    </tr>

    </tbody>
</table>