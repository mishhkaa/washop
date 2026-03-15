import asyncio
import logging
import os
import sys

# Один .env для всього проєкту (Laravel + бот): завантажити з кореня проєкту
try:
    from dotenv import load_dotenv
    load_dotenv(os.path.join(os.path.dirname(os.path.abspath(__file__)), ".env"))
except ImportError:
    pass

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

TOKEN = (os.getenv("TELEGRAM_BOT_TOKEN") or "").strip()
SHOP_WEBAPP_URL = (os.getenv("SHOP_WEBAPP_URL") or "").rstrip("/")

if not TOKEN:
    logger.error("TELEGRAM_BOT_TOKEN не задано. Додай у .env у корені проєкту (той самий файл, що й для Laravel).")
    sys.exit(1)

if not SHOP_WEBAPP_URL:
    logger.error("SHOP_WEBAPP_URL не задано. Додай у .env на сервері, наприклад: SHOP_WEBAPP_URL=https://mycrm.hookly.org")
    sys.exit(1)

# Щоб було видно, який саме сайт відкривається по кнопці в боті
logger.info("URL магазину (кнопка в боті): %s", SHOP_WEBAPP_URL)

bot = Bot(token=TOKEN)
dp = Dispatcher()


@dp.message(Command("start"))
async def start(message: Message):
    try:
        button_site = InlineKeyboardButton(
            text="🌐 Odwiedź nasz sklep",
            web_app=WebAppInfo(url=SHOP_WEBAPP_URL),
        )
        button_manager = InlineKeyboardButton(
            text="📞 Skontaktuj się z menadżerem",
            url="https://t.me/CloudCityManagerr",
        )
        keyboard = InlineKeyboardMarkup(inline_keyboard=[[button_site], [button_manager]])
        await message.answer(
            "🔥 Witaj w CloudCity - Twoim ulubionym sklepie vape! 🔥\n\n"
            "✨ Oferujemy:\n"
            "🎯 Wysokiej jakości kartridże\n"
            "💨 Najlepsze płyny (e-liquidy)\n"
            "🔋 Systemy i akcesoria\n"
            "⚡ Najnowsze trendy na rynku vape'u\n\n"
            "💯 Szybka dostawa\n"
            "🎁 Najlepsze ceny\n"
            "👥 Profesjonalna obsługa\n\n"
            "Kliknij poniżej, aby odkryć naszą pełną ofertę! 👇",
            reply_markup=keyboard,
        )
    except Exception as e:
        logger.exception("Помилка при обробці /start: %s", e)
        await message.answer("Виникла помилка. Спробуйте пізніше або зверніться do menadżera.")


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
