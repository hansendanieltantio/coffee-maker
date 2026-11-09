<script>
export const menuList = [
    {
        id: 4,
        name: "Kopi Hitam Murni",
        weight: 4,
        desc: "Ekstrak kopi murni tanpa pemanis. Prioritas standar.",
        icon: "☕",
        color: "bg-zinc-800", // Hitam pekat
    },
    {
        id: 3,
        name: "Kopi Hitam Dengan Gula",
        weight: 3,
        desc: "Kopi hitam klasik dengan sentuhan rasa manis.",
        icon: "🫖",
        color: "bg-amber-900", // Cokelat gelap
    },
    {
        id: 2,
        name: "Kopi Susu Saja",
        weight: 2,
        desc: "Paduan espresso dan susu creamy tanpa gula.",
        icon: "🥛",
        color: "bg-orange-200", // Krem susu
    },
    {
        id: 1,
        name: "Kopi Susu Dengan Gula",
        weight: 1,
        desc: "Varian paling lengkap dan manis. Prioritas tertinggi.",
        icon: "✨",
        color: "bg-yellow-600", // Emas/Cokelat cerah
    },
];

export default { name: "MenuPage" };
</script>
