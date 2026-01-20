pip install aiogram requests

BOT_TOKEN = "8206095710:AAG-8LomIwqO67yVv07d9mDe-yaiERnSyV4"
ADMIN_ID = @Samarqand2625

PANEL_API_KEY = "8206095710:AAG-8LomIwqO67yVv07d9mDe-yaiERnSyV4"
PANEL_URL ="https://justanotherpanel.com/api/v2"

CHANNEL_LINK = "https://t.me/bosthub77"

# TO‘LOV SOZLAMALARI
CLICK_MERCHANT_ID = "9860_0101_2223_9964"
PAYME_MERCHANT_ID = "PAYME_ID"

import sqlite3

db = sqlite3.connect("users.db")
sql = db.cursor()

sql.execute("""
CREATE TABLE IF NOT EXISTS users (
    user_id INTEGER PRIMARY KEY,
    balance INTEGER DEFAULT 0
)
""")
db.commit()

def get_balance(user_id):
    sql.execute("SELECT balance FROM users WHERE user_id=?", (user_id,))
    r = sql.fetchone()
    if not r:
        sql.execute("INSERT INTO users (user_id, balance) VALUES (?,?)", (user_id, 0))
        db.commit()
        return 0
    return r[0]

def add_balance(user_id, amount):
    sql.execute("UPDATE users SET balance = balance + ? WHERE user_id=?", (amount, user_id))
    db.commit()

def minus_balance(user_id, amount):
    sql.execute("UPDATE users SET balance = balance - ? WHERE user_id=?", (amount, user_id))
    db.commit()
    import logging, requests
from aiogram import Bot, Dispatcher, executor, types
from aiogram.types import InlineKeyboardMarkup, InlineKeyboardButton

logging.basicConfig(level=logging.INFO)

bot = Bot(token=BOT_TOKEN)
dp = Dispatcher(bot)

def panel_request(data):
    data["key"] = PANEL_API_KEY
    r = requests.post(PANEL_URL, data=data)
    return r.json()
    @dp.message_handler(commands=["start"])
async def start(m: types.Message):
    bal = get_balance(m.from_user.id)

    kb = InlineKeyboardMarkup(row_width=2)
    kb.add(
        InlineKeyboardButton("📸 Instagram", callback_data="insta"),
        InlineKeyboardButton("📢 Telegram", callback_data="tg"),
        InlineKeyboardButton("💰 Balans", callback_data="balance"),
        InlineKeyboardButton("➕ Balans to‘ldirish", callback_data="topup"),
        InlineKeyboardButton("📦 Buyurtma holati", callback_data="status"),
        InlineKeyboardButton("📣 Kanalimiz", url=CHANNEL_LINK)
    )

    await m.answer(
        f"🚀 <b>BoostHub77 SMM Bot</b>\n\n"
        f"💰 Balans: <b>{bal} so‘m</b>\n\n"
        "Xizmatni tanlang 👇",
        parse_mode="HTML",
        reply_markup=kb
    )
    @dp.callback_query_handler(lambda c: c.data == "balance")
async def balance(call):
    bal = get_balance(call.from_user.id)
    await call.message.answer(f"💰 Sizning balansingiz: <b>{bal} so‘m</b>", parse_mode="HTML")
    @dp.callback_query_handler(lambda c: c.data == "topup")
async def topup(call):
    kb = InlineKeyboardMarkup()
    kb.add(
        InlineKeyboardButton("💳 Click", callback_data="click"),
        InlineKeyboardButton("💳 Payme", callback_data="payme")
    )
    await call.message.answer("To‘lov turini tanlang 👇", reply_markup=kb)

@dp.callback_query_handler(lambda c: c.data in ["click", "payme"])
async def pay(call):
    await call.message.answer(
        "💰 Miqdorni yuboring (masalan: 10000)\n\n"
        "⚠️ Demo rejim: yuborsangiz balans qo‘shiladi"
    )@dp.message_handler(lambda m: m.text.isdigit())
async def demo_payment(m: types.Message):
    amount = int(m.text)
    if amount < 5000:
        await m.answer("❌ Minimal to‘lov: 5000 so‘m")
        return

    add_balance(m.from_user.id, amount)
    await m.answer(f"✅ {amount} so‘m balansga qo‘shildi")
    @dp.callback_query_handler(lambda c: c.data == "insta")
async def insta(call):
    await call.message.answer(
        "📸 Instagram follower\n"
        "💰 Narx: 10 000 so‘m (1000 dona)\n\n"
        "🔗 Profil linkini yuboring"
    )

@dp.message_handler(lambda m: "instagram.com" in m.text)
async def insta_order(m: types.Message):
    price = 10000
    bal = get_balance(m.from_user.id)

    if bal < price:
        await m.answer("❌ Balans yetarli emas")
        return

    minus_balance(m.from_user.id, price)

    res = panel_request({
        "action": "add",
        "service": 1,
        "link": m.text,
        "quantity": 1000
    })

    if "order" in res:
        await m.answer(f"✅ Buyurtma qabul qilindi\n🆔 Order ID: {res['order']}")
        await bot.send_message(ADMIN_ID, f"🆕 Buyurtma\nUser: @{m.from_user.username}")
    else:
        await m.answer("❌ Panel xatosi")
        @dp.callback_query_handler(lambda c: c.data == "status")
async def st(call):
    await call.message.answer("🆔 Order ID yuboring")

@dp.message_handler(lambda m: m.text.isdigit())
async def check(m):
    res = panel_request({"action": "status", "order": m.text})
    await m.answer(f"📦 Holat:\n<code>{res}</code>", parse_mode="HTML")
    if __name__ == "__main__":
    executor.start_polling(dp, skoip_updates=True)