const paypalButtons = window.paypal.Buttons({
  style: {
    shape: "rect",
    layout: "horizontal",
    color: "white",
    label: "paypal",
  },
  async createOrder() {
    try {
      const cart = [];
      const cartItems = document.querySelectorAll(
        ".cart-items p:not(.grand-total)"
      );
      cartItems.forEach((item) => {
        const name = item.querySelector(".name").textContent;
        const price = item.querySelector(".price").textContent.replace("$", "");
        const quantity = 1; // You may need to adjust this based on your cart structure
        cart.push({
          id: name,
          name: name,
          price: price,
          quantity: quantity,
        });
      });

      const response = await fetch("/api/orders", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          cart: cart,
          total_price: document
            .querySelector(".grand-total .price")
            .textContent.replace("$", ""),
        }),
      });

      const orderData = await response.json();

      if (orderData.id) {
        return orderData.id;
      }
      const errorDetail = orderData?.details?.[0];
      const errorMessage = errorDetail
        ? `${errorDetail.issue} ${errorDetail.description} (${orderData.debug_id})`
        : JSON.stringify(orderData);

      throw new Error(errorMessage);
    } catch (error) {
      console.error(error);
      resultMessage(`Could not initiate PayPal Checkout...<br><br>${error}`);
    }
  },
  async onApprove(data, actions) {
    try {
      const response = await fetch(`/api/orders/${data.orderID}/capture`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
      });

      const orderData = await response.json();
      const errorDetail = orderData?.details?.[0];

      if (errorDetail?.issue === "INSTRUMENT_DECLINED") {
        return actions.restart();
      } else if (errorDetail) {
        throw new Error(`${errorDetail.description} (${orderData.debug_id})`);
      } else if (!orderData.purchase_units) {
        throw new Error(JSON.stringify(orderData));
      } else {
        // Successful transaction
        const transaction =
          orderData?.purchase_units?.[0]?.payments?.captures?.[0] ||
          orderData?.purchase_units?.[0]?.payments?.authorizations?.[0];

        // Submit the form to process the order
        document.querySelector("form").submit();
      }
    } catch (error) {
      console.error(error);
      resultMessage(
        `Sorry, your transaction could not be processed...<br><br>${error}`
      );
    }
  },
});

paypalButtons.render("#paypal-button-container");

function resultMessage(message) {
  const container = document.querySelector("#result-message");
  if (container) {
    container.innerHTML = message;
  }
}
