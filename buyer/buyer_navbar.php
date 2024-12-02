<!-- Navigation Bar -->
<nav class="navbar navbar-expand-md bg-dark navbar-dark">
    <a class="navbar-brand" href="buyer_home.php">Buyer Dashboard</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="collapsibleNavbar">
      <ul class="navbar-nav ml-auto">
      <li class="nav-item">
          <a class="nav-link" href="buyer_home.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="update_profile.php"><?php echo $_SESSION["email"]; ?></a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="view_cart.php">Cart</a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="my_orders.php">My Orders</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="add_complain.php">Complaints</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="logout.php">Logout</a>
        </li>
      </ul>
    </div>
  </nav>