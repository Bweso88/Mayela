import 'package:material_ui/material_ui.dart';

import 'mayela_colors.dart';
import 'universe.dart';

/// Thème Material 3 de Mayela, décliné par univers (taille de texte, accents, formes).
abstract final class MayelaTheme {
  static ThemeData of(Universe universe) {
    final isJunior = universe == Universe.junior;
    final scheme = ColorScheme.fromSeed(
      seedColor: MayelaColors.primary,
      primary: MayelaColors.primary,
      secondary: MayelaColors.secondary,
      secondaryContainer: MayelaColors.mint,
      onSecondaryContainer: MayelaColors.onMint,
      tertiaryContainer: MayelaColors.peach,
      onTertiaryContainer: MayelaColors.onPeach,
      surface: MayelaColors.surface,
      surfaceContainerHighest: MayelaColors.lavender,
      error: MayelaColors.error,
      errorContainer: MayelaColors.errorContainer,
    );

    // Primaire : corps de texte plus grand (17) pour les jeunes lecteurs.
    final bodySize = isJunior ? 17.0 : 15.0;
    final radius = BorderRadius.circular(isJunior ? 20 : 16);

    final base = ThemeData(useMaterial3: true, colorScheme: scheme);
    final text = base.textTheme.apply(bodyColor: MayelaColors.textPrimary, displayColor: MayelaColors.textPrimary);

    return base.copyWith(
      scaffoldBackgroundColor: MayelaColors.surface,
      textTheme: text.copyWith(
        headlineMedium: text.headlineMedium?.copyWith(fontWeight: FontWeight.w700, fontSize: isJunior ? 28 : 26),
        titleLarge: text.titleLarge?.copyWith(fontWeight: FontWeight.w700, fontSize: isJunior ? 20 : 18),
        bodyLarge: text.bodyLarge?.copyWith(fontSize: bodySize, height: 1.5),
        bodyMedium: text.bodyMedium?.copyWith(fontSize: bodySize - 1, height: 1.45),
        labelLarge: text.labelLarge?.copyWith(fontWeight: FontWeight.w600, fontSize: 15),
      ),
      appBarTheme: const AppBarTheme(
        backgroundColor: MayelaColors.surface,
        foregroundColor: MayelaColors.primary,
        elevation: 0,
        centerTitle: false,
      ),
      cardTheme: CardThemeData(
        color: Colors.white,
        elevation: 0,
        shape: RoundedRectangleBorder(borderRadius: radius),
        margin: EdgeInsets.zero,
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          backgroundColor: MayelaColors.primary,
          foregroundColor: Colors.white,
          minimumSize: Size.fromHeight(isJunior ? 56 : 52),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          textStyle: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: MayelaColors.primary,
          backgroundColor: MayelaColors.lavender,
          side: BorderSide.none,
          minimumSize: Size.fromHeight(isJunior ? 56 : 52),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          textStyle: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: Colors.white,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: MayelaColors.lavender, width: 1.5),
        ),
      ),
    );
  }
}
