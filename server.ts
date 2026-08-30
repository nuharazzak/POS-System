import express from 'express';
import path from 'path';
import { createServer as createViteServer } from 'vite';

const app = express();
const PORT = 3000;

app.use(express.json());

// In-Memory SQLite / DB State Simulator for full-stack preview matching Laravel specs
const db = {
  settings: {
    id: 1,
    store_name: 'My Cafe & Bistro',
    address: '45 Bistro Lane, Downtown',
    phone: '+1 (555) 839-2041',
    currency: '$',
    tax_rate: 10.0,
    low_stock_threshold: 5
  },
  categories: [
    { id: 1, name: 'Food', description: 'Hearty meals, burgers, and rice dishes' },
    { id: 2, name: 'Drinks', description: 'Cold beverages, juices, coffees, and teas' },
    { id: 3, name: 'Snacks', description: 'Quick bites, fries, and finger foods' },
    { id: 4, name: 'Desserts', description: 'Sweet treats, pastries, and ice creams' },
    { id: 5, name: 'Combos', description: 'Value meal packages and daily specials' }
  ],
  products: [
    { id: 1, category_id: 1, name: 'Chicken Burger', description: 'Crispy breaded chicken patty with fresh lettuce and mayo', price: 5.00, stock_quantity: 25, is_active: true },
    { id: 2, category_id: 1, name: 'Beef Burger', description: 'Grilled beef patty with cheddar cheese, pickles, and house sauce', price: 6.50, stock_quantity: 20, is_active: true },
    { id: 3, category_id: 1, name: 'Chicken Rice', description: 'Fragrant jasmine rice with roasted tender chicken and chili dip', price: 7.00, stock_quantity: 18, is_active: true },
    { id: 4, category_id: 1, name: 'Fried Rice', description: 'Wok-tossed egg fried rice with vegetables and shrimp', price: 6.00, stock_quantity: 22, is_active: true },
    { id: 5, category_id: 1, name: 'Pizza Margherita', description: 'Handmade thin crust with tomato sauce, mozzarella, and basil', price: 9.50, stock_quantity: 15, is_active: true },
    { id: 6, category_id: 2, name: 'Cola', description: 'Chilled canned cola 330ml', price: 2.00, stock_quantity: 50, is_active: true },
    { id: 7, category_id: 2, name: 'Pepsi', description: 'Chilled canned pepsi 330ml', price: 2.00, stock_quantity: 45, is_active: true },
    { id: 8, category_id: 2, name: 'Fresh Juice', description: 'Freshly squeezed orange juice', price: 3.50, stock_quantity: 20, is_active: true },
    { id: 9, category_id: 2, name: 'Coffee', description: 'Freshly brewed dark roast espresso or americano', price: 3.00, stock_quantity: 40, is_active: true },
    { id: 10, category_id: 2, name: 'Iced Tea', description: 'Refreshing brewed lemon iced tea', price: 2.50, stock_quantity: 30, is_active: true },
    { id: 11, category_id: 3, name: 'French Fries', description: 'Crispy golden fries seasoned with sea salt', price: 2.50, stock_quantity: 35, is_active: true },
    { id: 12, category_id: 3, name: 'Chicken Nuggets', description: '6 pieces of crispy chicken nuggets with BBQ dip', price: 4.00, stock_quantity: 30, is_active: true },
    { id: 13, category_id: 3, name: 'Club Sandwich', description: 'Toasted triple-decker with chicken, egg, and fresh greens', price: 4.50, stock_quantity: 12, is_active: true },
    { id: 14, category_id: 4, name: 'Vanilla Ice Cream', description: 'Two scoops of creamy vanilla bean ice cream', price: 3.00, stock_quantity: 25, is_active: true },
    { id: 15, category_id: 4, name: 'Chocolate Cake', description: 'Rich Belgian dark chocolate fudge cake slice', price: 4.50, stock_quantity: 10, is_active: true }
  ],
  orders: [
    {
      id: 1,
      order_number: 'ORD-000001',
      user_id: 1,
      user_name: 'Admin Manager',
      subtotal: 12.50,
      discount_type: 'fixed',
      discount_value: 1.00,
      discount_amount: 1.00,
      tax_rate: 10.0,
      tax_amount: 1.15,
      total_amount: 12.65,
      payment_method: 'cash',
      amount_received: 20.00,
      change_amount: 7.35,
      status: 'completed',
      created_at: new Date(Date.now() - 3600000).toISOString(),
      items: [
        { id: 1, product_id: 1, product_name: 'Chicken Burger', quantity: 2, unit_price: 5.00, total_price: 10.00 },
        { id: 2, product_id: 11, product_name: 'French Fries', quantity: 1, unit_price: 2.50, total_price: 2.50 }
      ]
    }
  ],
  orderCounter: 2
};

// Helper: Format product with category and stock labels
function formatProduct(p) {
  const cat = db.categories.find(c => c.id === p.category_id);
  const lowThresh = db.settings.low_stock_threshold;
  return {
    ...p,
    category_name: cat ? cat.name : null,
    is_low_stock: p.stock_quantity <= lowThresh && p.stock_quantity > 0,
    is_out_of_stock: p.stock_quantity === 0
  };
}

// ----------------------------------------------------
// AUTH API
// ----------------------------------------------------
app.post('/api/auth/login', (req, res) => {
  const { email, password } = req.body;
  if (!email || !password) {
    return res.status(422).json({ success: false, message: 'Email and password are required.' });
  }

  if (email === 'admin@example.com' && password === 'password') {
    return res.json({
      success: true,
      message: 'Login successful.',
      data: {
        token: 'admin-sanctum-token-1',
        user: { id: 1, name: 'Admin Manager', email: 'admin@example.com', role: 'admin' }
      }
    });
  }

  if (email === 'cashier@example.com' && password === 'password') {
    return res.json({
      success: true,
      message: 'Login successful.',
      data: {
        token: 'cashier-sanctum-token-2',
        user: { id: 2, name: 'Jane Cashier', email: 'cashier@example.com', role: 'cashier' }
      }
    });
  }

  return res.status(422).json({
    success: false,
    message: 'Invalid email or password credentials.',
    errors: { email: ['The provided credentials do not match our records.'] }
  });
});

app.get('/api/auth/user', (req, res) => {
  const auth = req.headers.authorization;
  if (auth && auth.includes('admin')) {
    return res.json({ success: true, data: { id: 1, name: 'Admin Manager', email: 'admin@example.com', role: 'admin' } });
  }
  return res.json({ success: true, data: { id: 2, name: 'Jane Cashier', email: 'cashier@example.com', role: 'cashier' } });
});

app.post('/api/auth/logout', (req, res) => {
  res.json({ success: true, message: 'Logged out successfully.' });
});

// ----------------------------------------------------
// CATEGORIES API
// ----------------------------------------------------
app.get('/api/categories', (req, res) => {
  const list = db.categories.map(c => {
    const pCount = db.products.filter(p => p.category_id === c.id).length;
    return { ...c, products_count: pCount };
  });
  res.json({ success: true, data: list });
});

app.post('/api/categories', (req, res) => {
  const { name, description } = req.body;
  if (!name) return res.status(422).json({ success: false, message: 'Category name is required.' });
  const newCat = {
    id: db.categories.length > 0 ? Math.max(...db.categories.map(c => c.id)) + 1 : 1,
    name,
    description: description || ''
  };
  db.categories.push(newCat);
  res.status(201).json({ success: true, message: 'Category created successfully.', data: newCat });
});

app.put('/api/categories/:id', (req, res) => {
  const id = Number(req.params.id);
  const cat = db.categories.find(c => c.id === id);
  if (!cat) return res.status(404).json({ success: false, message: 'Category not found.' });
  if (req.body.name) cat.name = req.body.name;
  if (req.body.description !== undefined) cat.description = req.body.description;
  res.json({ success: true, message: 'Category updated successfully.', data: cat });
});

app.delete('/api/categories/:id', (req, res) => {
  const id = Number(req.params.id);
  const cat = db.categories.find(c => c.id === id);
  if (!cat) return res.status(404).json({ success: false, message: 'Category not found.' });

  const pCount = db.products.filter(p => p.category_id === id).length;
  if (pCount > 0) {
    return res.status(422).json({
      success: false,
      message: `Cannot delete category '${cat.name}'. There are ${pCount} product(s) assigned to it.`
    });
  }

  db.categories = db.categories.filter(c => c.id !== id);
  res.json({ success: true, message: 'Category deleted successfully.' });
});

// ----------------------------------------------------
// PRODUCTS API
// ----------------------------------------------------
app.get('/api/products', (req, res) => {
  let list = db.products;
  if (req.query.category_id) {
    list = list.filter(p => p.category_id === Number(req.query.category_id));
  }
  if (req.query.search) {
    const q = req.query.search.toLowerCase();
    list = list.filter(p => p.name.toLowerCase().includes(q));
  }
  if (req.query.active_only) {
    list = list.filter(p => p.is_active);
  }
  res.json({ success: true, data: list.map(formatProduct) });
});

app.get('/api/products/:id', (req, res) => {
  const p = db.products.find(p => p.id === Number(req.params.id));
  if (!p) return res.status(404).json({ success: false, message: 'Product not found.' });
  res.json({ success: true, data: formatProduct(p) });
});

app.post('/api/products', (req, res) => {
  const { name, category_id, price, stock_quantity, description, is_active } = req.body;
  if (!name || !category_id || price === undefined || stock_quantity === undefined) {
    return res.status(422).json({ success: false, message: 'Missing required product fields.' });
  }
  const newProd = {
    id: db.products.length > 0 ? Math.max(...db.products.map(p => p.id)) + 1 : 1,
    name,
    category_id: Number(category_id),
    price: Number(price),
    stock_quantity: Number(stock_quantity),
    description: description || '',
    is_active: is_active !== undefined ? is_active : true
  };
  db.products.push(newProd);
  res.status(201).json({ success: true, message: 'Product created successfully.', data: formatProduct(newProd) });
});

app.put('/api/products/:id', (req, res) => {
  const id = Number(req.params.id);
  const p = db.products.find(p => p.id === id);
  if (!p) return res.status(404).json({ success: false, message: 'Product not found.' });
  if (req.body.name) p.name = req.body.name;
  if (req.body.category_id) p.category_id = Number(req.body.category_id);
  if (req.body.price !== undefined) p.price = Number(req.body.price);
  if (req.body.stock_quantity !== undefined) p.stock_quantity = Number(req.body.stock_quantity);
  if (req.body.description !== undefined) p.description = req.body.description;
  if (req.body.is_active !== undefined) p.is_active = Boolean(req.body.is_active);
  res.json({ success: true, message: 'Product updated successfully.', data: formatProduct(p) });
});

app.delete('/api/products/:id', (req, res) => {
  const id = Number(req.params.id);
  const index = db.products.findIndex(p => p.id === id);
  if (index === -1) return res.status(404).json({ success: false, message: 'Product not found.' });
  db.products.splice(index, 1);
  res.json({ success: true, message: 'Product deleted successfully.' });
});

// ----------------------------------------------------
// ORDERS API (Strict DB validation, calculation & stock deduction)
// ----------------------------------------------------
app.post('/api/orders', (req, res) => {
  const { items, discount_type, discount_value, payment_method, amount_received } = req.body;

  if (!items || !Array.isArray(items) || items.length === 0) {
    return res.status(422).json({ success: false, message: 'Order must contain at least one item.' });
  }

  // 1. Fetch products from DB & check stock
  let subtotal = 0;
  const orderItems = [];

  for (const itemReq of items) {
    const product = db.products.find(p => p.id === itemReq.product_id);
    if (!product) {
      return res.status(422).json({ success: false, message: `Product ID #${itemReq.product_id} not found.` });
    }
    if (product.stock_quantity < itemReq.quantity) {
      return res.status(422).json({
        success: false,
        message: `Insufficient stock for '${product.name}'. Available: ${product.stock_quantity}, requested: ${itemReq.quantity}.`
      });
    }

    const itemTotal = product.price * itemReq.quantity;
    subtotal += itemTotal;

    orderItems.push({
      id: orderItems.length + 1,
      product_id: product.id,
      product_name: product.name,
      unit_price: product.price,
      quantity: itemReq.quantity,
      total_price: itemTotal
    });
  }

  // 2. Calculate discount
  let discountAmount = 0;
  const discVal = Number(discount_value) || 0;
  if (discount_type === 'percentage') {
    const pct = Math.min(100, Math.max(0, discVal));
    discountAmount = (subtotal * pct) / 100;
  } else if (discount_type === 'fixed') {
    discountAmount = Math.min(subtotal, Math.max(0, discVal));
  }

  // 3. Calculate tax
  const taxRate = db.settings.tax_rate;
  const taxable = Math.max(0, subtotal - discountAmount);
  const taxAmount = (taxable * taxRate) / 100;
  const totalAmount = taxable + taxAmount;

  // 4. Validate payment
  const received = Number(amount_received) || totalAmount;
  if (payment_method === 'cash' && received < totalAmount) {
    return res.status(422).json({
      success: false,
      message: `Amount received ($${received.toFixed(2)}) cannot be less than total ($${totalAmount.toFixed(2)}).`
    });
  }

  const changeAmount = payment_method === 'cash' ? Math.max(0, received - totalAmount) : 0;

  // 5. Transaction: Deduct stock
  for (const itemReq of items) {
    const product = db.products.find(p => p.id === itemReq.product_id);
    product.stock_quantity -= itemReq.quantity;
  }

  // 6. Create Order record
  const orderNumber = `ORD-${String(db.orderCounter++).padStart(6, '0')}`;
  const newOrder = {
    id: db.orders.length + 1,
    order_number: orderNumber,
    user_id: 1,
    user_name: 'Admin Manager',
    subtotal: Number(subtotal.toFixed(2)),
    discount_type: discount_type || 'percentage',
    discount_value: discVal,
    discount_amount: Number(discountAmount.toFixed(2)),
    tax_rate: taxRate,
    tax_amount: Number(taxAmount.toFixed(2)),
    total_amount: Number(totalAmount.toFixed(2)),
    payment_method: payment_method || 'cash',
    amount_received: Number(received.toFixed(2)),
    change_amount: Number(changeAmount.toFixed(2)),
    status: 'completed',
    created_at: new Date().toISOString(),
    items: orderItems
  };

  db.orders.unshift(newOrder);

  res.status(201).json({
    success: true,
    message: 'Order completed successfully.',
    data: newOrder
  });
});

app.get('/api/orders', (req, res) => {
  let list = db.orders;
  if (req.query.search) {
    const q = req.query.search.toLowerCase();
    list = list.filter(o => o.order_number.toLowerCase().includes(q));
  }
  if (req.query.payment_method) {
    list = list.filter(o => o.payment_method === req.query.payment_method);
  }
  if (req.query.date) {
    list = list.filter(o => o.created_at.startsWith(req.query.date));
  }
  res.json({ success: true, data: list });
});

app.get('/api/orders/:id', (req, res) => {
  const order = db.orders.find(o => o.id === Number(req.params.id));
  if (!order) return res.status(404).json({ success: false, message: 'Order not found.' });
  res.json({ success: true, data: order });
});

// ----------------------------------------------------
// REPORTS & DASHBOARD API
// ----------------------------------------------------
app.get('/api/reports/dashboard', (req, res) => {
  const completedOrders = db.orders.filter(o => o.status === 'completed');
  const todaySales = completedOrders.reduce((sum, o) => sum + o.total_amount, 0);
  const lowThresh = db.settings.low_stock_threshold;
  const lowStock = db.products.filter(p => p.stock_quantity <= lowThresh).length;

  // Best sellers aggregation
  const itemMap = {};
  for (const order of completedOrders) {
    for (const item of order.items) {
      if (!itemMap[item.product_id]) {
        itemMap[item.product_id] = {
          product_id: item.product_id,
          product_name: item.product_name,
          quantity_sold: 0,
          revenue: 0
        };
      }
      itemMap[item.product_id].quantity_sold += item.quantity;
      itemMap[item.product_id].revenue += item.total_price;
    }
  }

  const bestSellers = Object.values(itemMap).sort((a, b) => b.quantity_sold - a.quantity_sold).slice(0, 5);

  res.json({
    success: true,
    data: {
      stats: {
        today_sales: todaySales,
        today_orders: completedOrders.length,
        total_products: db.products.filter(p => p.is_active).length,
        low_stock_products: lowStock,
        low_stock_threshold: lowThresh
      },
      recent_orders: completedOrders.slice(0, 5),
      best_sellers: bestSellers
    }
  });
});

app.get('/api/reports/sales', (req, res) => {
  const completed = db.orders.filter(o => o.status === 'completed');
  const totalSales = completed.reduce((sum, o) => sum + o.total_amount, 0);
  const cashSales = completed.filter(o => o.payment_method === 'cash').reduce((sum, o) => sum + o.total_amount, 0);
  const cardSales = completed.filter(o => o.payment_method === 'card').reduce((sum, o) => sum + o.total_amount, 0);
  const onlineSales = completed.filter(o => o.payment_method === 'online').reduce((sum, o) => sum + o.total_amount, 0);

  res.json({
    success: true,
    data: {
      total_sales: totalSales,
      total_orders: completed.length,
      average_order_value: completed.length > 0 ? totalSales / completed.length : 0,
      cash_sales: cashSales,
      card_sales: cardSales,
      online_sales: onlineSales
    }
  });
});

app.get('/api/reports/best-selling-products', (req, res) => {
  const completed = db.orders.filter(o => o.status === 'completed');
  const itemMap = {};
  for (const order of completed) {
    for (const item of order.items) {
      if (!itemMap[item.product_id]) {
        itemMap[item.product_id] = {
          product_id: item.product_id,
          product_name: item.product_name,
          quantity_sold: 0,
          revenue: 0
        };
      }
      itemMap[item.product_id].quantity_sold += item.quantity;
      itemMap[item.product_id].revenue += item.total_price;
    }
  }
  const bestSellers = Object.values(itemMap).sort((a, b) => b.quantity_sold - a.quantity_sold);
  res.json({ success: true, data: bestSellers });
});

// ----------------------------------------------------
// SETTINGS API
// ----------------------------------------------------
app.get('/api/settings', (req, res) => {
  res.json({ success: true, data: db.settings });
});

app.put('/api/settings', (req, res) => {
  const { store_name, address, phone, currency, tax_rate, low_stock_threshold } = req.body;
  if (store_name) db.settings.store_name = store_name;
  if (address !== undefined) db.settings.address = address;
  if (phone !== undefined) db.settings.phone = phone;
  if (currency) db.settings.currency = currency;
  if (tax_rate !== undefined) db.settings.tax_rate = Number(tax_rate);
  if (low_stock_threshold !== undefined) db.settings.low_stock_threshold = Number(low_stock_threshold);

  res.json({ success: true, message: 'Settings updated successfully.', data: db.settings });
});

// ----------------------------------------------------
// VITE MIDDLEWARE SETUP
// ----------------------------------------------------
async function start() {
  if (process.env.NODE_ENV !== 'production') {
    const vite = await createViteServer({
      server: { middlewareMode: true },
      appType: 'spa'
    });
    app.use(vite.middlewares);
  } else {
    const distPath = path.join(process.cwd(), 'dist');
    app.use(express.static(distPath));
    app.get('*', (req, res) => {
      res.sendFile(path.join(distPath, 'index.html'));
    });
  }

  app.listen(PORT, '0.0.0.0', () => {
    console.log(`POS Full-Stack Server running on port ${PORT}`);
  });
}

start();
