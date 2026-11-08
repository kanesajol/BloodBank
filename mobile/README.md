# Mobile app moved

The Expo app lives in the **BLOODBANK project root** (same folder as Laravel), not in this `mobile/` directory.

From `c:\wamp64\www\BLOODBANK`:

```powershell
php artisan serve --host=0.0.0.0 --port=8000
npx expo start
```

Press **w** for web, or scan the QR with Expo Go.

Config: `src\config.js`

Setup, demo accounts, and the API are documented in the project root:

- [README](../README.md)
- [Staff guide](../docs/staff-guide.md)
- [API](../docs/api.md)
