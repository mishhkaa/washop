import asyncio
import logging
import os
import sys

from aiogram import Bot, Dispatcher
from aiogram.types import Message, InlineKeyboardMarkup, InlineKeyboardButton, WebAppInfo
from aiogram.filters import Command

# Щоб бот не падав мовчки — логи в консоль
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(message)s",
    handlers=[logging.StreamHandler(sys.stdout)],
)
logger = logging.getLogger(__name__)

TOKEN = os.getenv("TELEGRAM_BOT_TOKEN", "8245246326:AAEQaA3eWsBqaJLFgfZ92kwSNXey3zDyT84")
SHOP_WEBAPP_URL = (os.getenv("SHOP_WEBAPP_URL", "https://mycrm.hookly.org/") or "").rstrip("/")

if not TOKEN:
    logger.error("TELEGRAM_BOT_TOKEN не задано. Встановіть змінну середовища або додайте токен у bot.py.")
    sys.exit(1)

if not SHOP_WEBAPP_URL:
    logger.error("SHOP_WEBAPP_URL не задано.")
    sys.exit(1)

bot = Bot(token=TOKEN)
dp = Dispatcher()


@dp.message(Command("start"))
async def start(message: Message):
    try:
        button = InlineKeyboardButton(
            text="Відкрити магазин",
            web_app=WebAppInfo(url=SHOP_WEBAPP_URL),
        )
        keyboard = InlineKeyboardMarkup(inline_keyboard=[[button]])
        await message.answer(
            "Натисни кнопку — відкриється магазин у Telegram. "
            "Оформлення замовлення автоматично передасться в CRM.",
            reply_markup=keyboard,
        )
    except Exception as e:
        logger.exception("Помилка при обробці /start: %s", e)
        await message.answer("Виникла помилка. Спробуйте пізніше або зверніться до адміністратора.")


async def main():
    try:
        logger.info("Бот запускається... (WebApp: %s)", SHOP_WEBAPP_URL)
        await dp.start_polling(bot)
    except Exception as e:
        logger.exception("Помилка запуску бота: %s", e)
        raise
    finally:
        await bot.session.close()


if __name__ == "__main__":
    try:
        asyncio.run(main())
    except KeyboardInterrupt:
        logger.info("Бот зупинено.")
    except Exception as e:
        logger.exception("Критична помилка: %s", e)
        sys.exit(1)
