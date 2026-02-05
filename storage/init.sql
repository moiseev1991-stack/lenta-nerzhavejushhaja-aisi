-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    slug TEXT UNIQUE NOT NULL,
    name TEXT NOT NULL,
    h1 TEXT,
    title TEXT,
    description TEXT,
    intro TEXT,
    is_active INTEGER DEFAULT 1,
    content_title TEXT,
    content_body TEXT,
    content_format TEXT DEFAULT 'markdown',
    content_is_active INTEGER DEFAULT 0,
    content_updated_at TEXT,
    created_at TEXT,
    updated_at TEXT
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER NOT NULL,
    slug TEXT UNIQUE NOT NULL,
    name TEXT NOT NULL,
    h1 TEXT,
    title TEXT,
    description TEXT,
    thickness REAL,
    width REAL,
    condition TEXT,
    spring INTEGER DEFAULT 0,
    surface TEXT,
    price_per_kg REAL,
    in_stock INTEGER DEFAULT 1,
    lead_time TEXT,
    image TEXT,
    created_at TEXT,
    updated_at TEXT,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Indexes
CREATE INDEX IF NOT EXISTS idx_products_category ON products(category_id);
CREATE INDEX IF NOT EXISTS idx_products_slug ON products(slug);
CREATE INDEX IF NOT EXISTS idx_categories_slug ON categories(slug);
CREATE INDEX IF NOT EXISTS idx_categories_active ON categories(is_active);

-- Static pages table (for /bonus/ and similar)
CREATE TABLE IF NOT EXISTS pages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    slug TEXT UNIQUE NOT NULL,
    title TEXT NOT NULL,
    content TEXT NOT NULL,
    updated_at TEXT
);
