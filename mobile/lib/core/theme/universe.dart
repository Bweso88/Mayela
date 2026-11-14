/// Les trois univers visuels de Mayela.
enum Universe {
  /// CE1 → CM2 : aventure gamifiée avec Léo.
  junior,

  /// 6e → 3e.
  college,

  /// Seconde → Terminale.
  lycee;

  /// Univers renvoyé par l'API pour la classe de l'élève (`junior`, `college`, `lycee`).
  static Universe? fromCode(String? code) => switch (code) {
    'junior' => Universe.junior,
    'college' => Universe.college,
    'lycee' => Universe.lycee,
    _ => null,
  };

  /// Repli selon l'âge tant que la classe de l'élève n'est pas renseignée.
  static Universe fromAge(int age) {
    if (age < 11) return Universe.junior;
    if (age < 15) return Universe.college;
    return Universe.lycee;
  }

  String get label => switch (this) {
    Universe.junior => 'Mayela Junior',
    Universe.college => 'Mayela Collège',
    Universe.lycee => 'Mayela Lycée',
  };
}
